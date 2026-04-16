<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\MeasurementUnit;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MeasurementUnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:measurement-units_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:measurement-units_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:measurement-units_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:measurement-units_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data =  MeasurementUnit::orderBy('name')->paginate(10);

        return view('measurement-units.index', compact('data'));
    }

    public function create()
    {
        return view('measurement-units.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        MeasurementUnit::create($request->all());

        return redirect()->route('measurement-units.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = MeasurementUnit::findOrFail($id);

        return view('measurement-units.show', compact('item'));
    }

    public function edit($id)
    {
        $item = MeasurementUnit::findOrFail($id);

        return view('measurement-units.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = MeasurementUnit::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('measurement-units.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = MeasurementUnit::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('measurement-units.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('measurement-units.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'initials'=> ['required','max:4', Rule::unique('measurement_units')->ignore($primaryKey)],
            'name'=> ['required','max:20'],
            'is_enabled'=> ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
