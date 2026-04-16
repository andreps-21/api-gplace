<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:parameters_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:parameters_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:parameters_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:parameters_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data =  Parameter::orderBy('name')->paginate(10);

        return view('parameters.index', compact('data'));
    }

    public function create()
    {
        return view('parameters.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules($request));

        Parameter::create($request->all());

        return redirect()->route('parameters.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Parameter::findOrFail($id);

        return view('parameters.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Parameter::findOrFail($id);

        return view('parameters.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Parameter::findOrFail($id);

        $this->validate($request, $this->rules($request, $id));

        $inputs = $request->all();

        $item->fill($inputs)->save();

        return redirect()->route('parameters.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Parameter::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('parameters.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('parameters.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30', Rule::unique('permissions')->ignore($primaryKey)],
            'type' => 'required',
            'value' => 'required|max:250',
            'description' => 'nullable|max:120'
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
