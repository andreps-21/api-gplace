<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permissions_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permissions_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permissions_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:permissions_delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        $data =  Permission::orderBy('description')->paginate(10);

        return view('permissions.index', compact('data'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules($request));


        Permission::create($request->all());

        return redirect()->route('permissions.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Permission::findOrFail($id);

        return view('permissions.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Permission::findOrFail($id);

        return view('permissions.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Permission::findOrFail($id);

        $this->validate($request, $this->rules($request, $item->getKey()));

        $item->fill($request->all())->save();

        return redirect()->route('permissions.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Permission::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('permissions.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('permissions.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:15', Rule::unique('permissions')->ignore($primaryKey)],
            'description' => ['required', 'max:40']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
