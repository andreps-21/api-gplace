<?php

namespace App\Http\Controllers\Admin;

use App\Rules\CpfCnpj;
use App\Models\Profession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProfessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:professions_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:professions_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:professions_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:professions_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Profession::orderBy('name')->paginate(10);

        return view('professions.index', compact('data'));
    }

    public function create()
    {
        return view('professions.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        Profession::create($request->all());

        return redirect()->route('professions.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Profession::findOrFail($id);

        return view('professions.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Profession::findOrFail($id);

        return view('professions.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Profession::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->all())->save();

        return redirect()->route('professions.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Profession::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('professions.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('professions.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name'=> ['required', 'max:35', Rule::unique('professions')->ignore($primaryKey)],
            'is_enabled'=> ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
