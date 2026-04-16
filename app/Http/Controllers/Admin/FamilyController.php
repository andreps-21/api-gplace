<?php

namespace App\Http\Controllers\Admin;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FamilyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:families_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:families_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:families_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:families_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Family::orderBy('name')
        ->paginate(10);

        return view('families.index', compact('data'));
    }

    public function create()
    {
        return view('families.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        Family::create($request->all());

        return redirect()->route('families.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Family::findOrFail($id);

        return view('families.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Family::findOrFail($id);

        return view('families.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Family::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('families.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Family::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('families.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('families.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30', Rule::unique('families')->ignore($primaryKey)],
            'is_enabled' => 'required'
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
