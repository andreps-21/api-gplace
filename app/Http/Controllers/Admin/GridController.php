<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Grid;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GridController extends Controller
{

    public function __construct(){
    $this->middleware('permission:grid_create', ['only' => ['create', 'store']]);
    $this->middleware('permission:grid_edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:grid_view', ['only' => ['show', 'index']]);
    $this->middleware('permission:grid_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Grid::with('variation')->orderBy('grid')->paginate(10);
        return view('grid.index', compact('data'));
    }

    public function create()
    {
        return view('grid.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['grid'] = $request->grid;
            $inputs['description'] = $request->description;
            $inputs['is_enabled'] = $request->is_enabled;
            $inputs['type'] =  $request->type;
            $grid = Grid::create($inputs);

            $total = count($request->abbreviation);
            for ($i = 0; $i < $total; $i++)
            {
                $grid->variation()->create([
                    'abbreviation' => $request->abbreviation[$i],
                    'variation' => $request->var_description[$i],
                    'is_enabled' => $request->var_is_enabled[$i],
                    'representation' => $request->representation[$i]
                ]);
            }

            DB::commit();

                return redirect()->route('grid.index')
                ->withStatus('Registro criado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grid.index')
                ->withError('Registro não inserido');
        }
    }

    public function show($id)
    {
        $item = Grid::with('variation')->findOrFail($id);
        return view('grid.show', compact('item'));
    }

    public function edit($id)
    {
       $data = Grid::with('variation')->findOrFail($id);
        return view('grid.edit', compact('data'));

    }

    public function update(Request $request, $id)
    {
        $item = Grid::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        try {
            DB::beginTransaction();
            $item->update([
                'grid' => $request->grid,
                'description' => $request->description,
                'is_enabled' => $request->is_enabled,
                'type' => $request->type,
            ]);

                $total = count($request->abbreviation);
                for ($i = 0; $i < $total; $i++)
                {
                    $item->variation()->updateOrCreate([

                        'id' => $request->code[$i],
                    ],
                    [
                        'abbreviation' => $request->abbreviation[$i],
                        'variation' => $request->var_description[$i],
                        'is_enabled' => $request->var_is_enabled[$i],
                        'representation' =>$request->representation[$i]
                    ]);

                }
                DB::commit();

                return redirect()->route('grid.index')
                ->withStatus('Registro atualizado com sucesso.');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('grid.index')
                    ->withError('Registro não atualizado.'.$e->getMessage());
            }
    }

    public function destroy($id)
    {
        $item = Grid::with('variation')->findOrFail($id);

        try {
            DB::beginTransaction();
            $item->variation()->delete();
            $item->delete();
            DB::commit();
            return redirect()->route('grid.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grid.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
           'grid' => ['required','max:20'],
           'description' => ['max:40'],
           'is_enabled' => ['required'],
           'abbreviation' => ['required', 'array'],
           'var_description' => ['required', 'array'],
           'var_is_enabled' => ['required', 'array'],
           'representation' => ['required', 'array'],
           'type' => ['required','max:20']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }


}
