<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogs_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:catalogs_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:catalogs_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:catalogs_delete', ['only' => ['destroy']]);
        $this->middleware('store');
    }
    public function index(Request $request)
    {
        $data =  Catalog::where('store_id', session('store')['id'])->orderBy('name')->paginate(10);

        return view('catalogs.index', compact('data'));
    }

    public function create(Request $request)
    {

        return view('catalogs.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = new Catalog($request->all());
        $item->store_id = session('store')['id'];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('catalogs', 'public');

            $item->image = $upload;
        }

        $item->save();

        return redirect()->route('catalogs.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Catalog::findOrFail($id);

        return view('catalogs.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Catalog::findOrFail($id);

        return view('catalogs.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Catalog::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all());

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('catalogs', 'public');

            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->image = $upload;
        }

        $item->save();

        return redirect()->route('catalogs.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Catalog::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('catalogs.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('catalogs.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:120'],
            'url' => ['required'],
            'image' => ['nullable'],
            'text_email' => ['nullable'],
            'is_enabled' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
