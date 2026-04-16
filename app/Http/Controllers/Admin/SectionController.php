<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Kalnoy\Nestedset\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sections_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sections_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sections_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:sections_delete', ['only' => ['destroy']]);
        $this->middleware('store');
    }
    public function index()
    {
        $data =  Section::withDepth()
            ->withCount('descendants')
            ->where('store_id', session('store')['id'])
            ->get()
            ->toFlatTree();

        return view('sections.index', compact('data'));
    }

    public function create(Request $request)
    {
        $data = $request->parent_id;

        $sections = $this->getPlanOptions();

        $types = Section::types();

        $section = Section::withDepth()->find($data);

        if (!$section) {
            $types = array_splice($types, 0, 1);
        } else if ($section->depth == 1) {
            $types = array_splice($types, 1, 1);
        }

        return view('sections.create', compact(
            'data',
            'sections',
            'types'
        ));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['use'] = 1;
        $inputs['store_id'] = session('store')['id'];

        $item = new Section($inputs);

        $order_home = Section::where('store_id', session('store')['id'])->where('order_home', $item->order_home)->where('parent_id', $item->parent_id)->first();//ordem home
        $cost_order = Section::where('store_id', session('store')['id'])->where('order', $item->order)->where('parent_id', $item->parent_id)->first();//ordem menu
        // dd($cost_order);
        if($item->order_home == null)
        {
            if ($cost_order){
                return redirect()->route('sections.index')
                ->withError('Dados invalidos! Ordem Menu deve ter uma opção diferente.');
            }

        }else{
            if($order_home || $cost_order){
                return redirect()->route('sections.index')
                ->withError('Dados invalidos! Posição Home e Ordem Menu, devem ter opções diferentes.');
            }
        }
        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $file = $request->file('image');

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                Storage::disk('public')->put('sections' . '/' . $filename, file_get_contents($file));
                Storage::disk('public')->put('sections/mobile' . '/' . $filename, file_get_contents($file));

            $item->filename = 'sections/' . $filename;
        }

        $item->save();

        return redirect()->route('sections.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Section::findOrFail($id);

        return view('sections.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        $sections = $this->getPlanOptions();

        $data = $item->parent_id;

        $types = Section::types();

        $section = Section::withDepth()->find($data);

        if (!$section) {
            $types = array_splice($types, 0, 1);
        } else if ($section->depth == 1) {
            $types = array_splice($types, 1, 1);
        }

        return view('sections.edit', compact(
            'item',
            'sections',
            'data',
            'types'
        ));
    }

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->all());

        $order_home = Section::where('store_id', session('store')['id'])->where('order_home',$request->order_home )->where('id', '!=', $id)->where('parent_id', $item->parent_id)->first();//ordem home
        $cost_order = Section::where('store_id', session('store')['id'])->where('order',$request->order )->where('id', '!=', $id)->where('parent_id', $item->parent_id)->first();//ordem menu

        if($request->order_home == null)
        {
            if ($cost_order){
                return redirect()->route('sections.index')
                ->withError('Dados invalidos! Ordem Menu deve ter uma opção diferente.');
            }

        }else{
            if($order_home || $cost_order){
                return redirect()->route('sections.index')
                ->withError('Dados invalidos! Posição Home e Ordem Menu, devem ter opções diferentes.');
            }
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $file = $request->file('image');

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            try{
                Storage::disk('public')->put('sections' . '/' . $filename, file_get_contents($file));
                Storage::disk('public')->put('sections/mobile' . '/' . $filename, file_get_contents($file));
            } catch (\Exception $e) {
                Log::error("erro ao salvar Seção. Seção ID:". $item->id . $e->getMessage());
                return redirect()->route('sections.index')
                ->withErrors('Ocorreu um erro ao fazer Upload da Imagem.');
            }

            if ($item->filename) {
                Storage::delete($item->filename);
            }

            $item->filename = 'sections/' . $filename;
        }

        $item->save();

        return redirect()->route('sections.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Section::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('sections.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('sections.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'min:3', 'max:35'],
            'descriptive' => ['nullable', 'max:120'],
            'type' => ['required'],
            'is_enabled' => ['required'],
            'order' => ['required'],
            'is_home' => ['required', 'boolean'],
            'order_home' => ['nullable', 'integer', 'min:0']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }

    /**
     * @param Collection $items
     *
     * @return static
     */
    protected function makeOptions(Collection $items)
    {
        $options = ['' => 'Não Contém'];
        foreach ($items as $item) {
            $options[$item->getKey()] = $item->name;
        }
        return $options;
    }


    protected function getPlanOptions($except = null)
    {
        /** @var \Kalnoy\Nestedset\QueryBuilder $query */

        $query = Section::select('id', 'name')->withDepth();

        if ($except) {
            $query->whereNotDescendantOf($except)->where('id', '<>', $except->id);
        }
        return $this->makeOptions($query->get());
    }
}
