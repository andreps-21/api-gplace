<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Rule as RuleModel;
use App\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:roles_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:roles_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:roles_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data =  Role::query()
            ->select(
                'roles.*',
                DB::raw('case when roles.tenant_id is null then roles.description else concat(people.formal_name, " - ", roles.description) end as description')
            )
            ->leftJoin('tenants', 'tenants.id', '=', 'roles.tenant_id')
            ->leftJoin('people', 'people.id', '=', 'tenants.person_id')
            ->when(session()->has('tenant'), function ($query) {
                $query->where('tenant_id', session('tenant')['id']);
            })
            ->when(!empty($request->search), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('roles.name', 'LIKE', "%$request->search%")
                    ->orWhere('roles.description', 'LIKE', "%$request->search%");
                });
            })
            ->when(!empty($request->start_date), function ($q) use ($request) {
                return $q->whereDate('roles.created_at', '>=', $request->start_date);
            })
            ->when(!empty($request->end_date), function ($q) use ($request) {
                return $q->whereDate('roles.created_at', '<=', $request->end_date);
            })
            ->orderBy('description')
            ->paginate(10);

        return view('roles.index', compact('data'));
    }

    public function create()
    {
        $permissions = Permission::PERMISSIONS;
        
        if (session()->has('tenant')) {
            /** @var User $user */
            $user = auth()->user();

            $userPermissions = $user->getPermissionsViaRoles()->pluck('name')->all();

            $permissions = $this->checkPermissionsTenant($permissions, $userPermissions);
        }

        $rules = RuleModel::orderBy('name')->get();

        return view('roles.create', compact('permissions', 'rules'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = Role::create($request->except('permissions'));

        $item->givePermissionTo($request->permissions);

        if (!empty($request->rules)) {
            $item->rules()->attach($request->rules);
        }

        return redirect()->route('roles.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Role::with('permissions')->findOrFail($id);

        return view('roles.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Role::with('permissions', 'rules')->findOrFail($id);

        $permissions = Permission::PERMISSIONS;

        if (session()->has('tenant')) {
            /** @var User $user */
            $user = auth()->user();

            $userPermissions = $user->getPermissionsViaRoles()->pluck('name')->all();

            $permissions = $this->checkPermissionsTenant($permissions, $userPermissions);
        }

        $rules = RuleModel::orderBy('name')->get();

        $rulesRole = $item->rules->modelKeys();

        return view('roles.edit', compact('item', 'permissions', 'rules', 'rulesRole'));
    }

    public function update(Request $request, $id)
    {
        $item = Role::with('permissions')->findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        DB::transaction(function () use ($item, $request) {
            $item->fill($request->except('permissions'))->save();
            $item->permissions()->detach();
            $item->givePermissionTo($request->permissions);

            $rules = [];
            if (!empty($request->rules)) {
                $rules = $request->rules;
            }
            $item->rules()->sync($rules);
        });

        return redirect()->route('roles.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Role::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('roles.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:40', Rule::unique('roles')->ignore($primaryKey)],
            'description' => ['required', 'max:40'],
            'permissions' => ['required', 'array']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }



    private function checkPermissionsTenant(array &$permissions, array $userPermissions)
    {
        foreach ($permissions as  $key => &$value) {
            if (!is_array($value) && !empty($value)) {
                return;
            }

            if (
                array_key_exists('title', $value) &&
                !empty($value['items'])
            ) {
                $value['items'] = $this->checkPermissionsTenant($value['items'], $userPermissions);
            }

            if (
                array_key_exists('title', $value) &&
                empty($value['items'])
            ) {
                unset($permissions[$key]);
            }

            if (
                array_key_exists('name', $value) &&
                !in_array($value['name'], $userPermissions)
            ) {
                unset($permissions[$key]);
            }
        }
        return $permissions;
    }
}
