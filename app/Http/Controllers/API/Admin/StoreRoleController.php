<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

/**
 * Detalhe de role do tenant e sincronização de permissões Spatie (substitui fluxo Blade).
 */
class StoreRoleController extends BaseController
{
    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    /**
     * Papéis com tenant_id null são modelos globais (ex.: contratante, administrador).
     * Só quem edita permissões ao nível da plataforma deve sincronizar esses papéis.
     */
    private function userMayManageGlobalRoles(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }
        if (strcasecmp(trim((string) $user->email), 'admin@gooding.solutions') === 0) {
            return true;
        }
        if ($user->can('permissions_edit')) {
            return true;
        }
        foreach (['administrador', 'Administrador', 'master', 'Master'] as $roleName) {
            if ($user->hasRole($roleName, 'web')) {
                return true;
            }
        }

        return false;
    }

    private function resolveRoleForStore(Request $request, int $id): Role
    {
        $tenantId = $this->tenantId($request);
        $role = Role::query()->whereKey($id)->firstOrFail();

        if ($role->tenant_id === null) {
            return $role;
        }

        if ((int) $role->tenant_id !== $tenantId) {
            abort(403, 'Este papel não pertence ao tenant da loja.');
        }

        return $role;
    }

    public function show(Request $request, int $id)
    {
        if (! $request->user()?->can('roles_view')) {
            abort(403);
        }

        $role = $this->resolveRoleForStore($request, $id)->load('permissions');

        return $this->sendResponse([
            'role' => $role,
            'permission_ids' => $role->permissions->pluck('id')->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()?->can('roles_create')) {
            abort(403);
        }

        $tenantId = $this->tenantId($request);
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:40',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('guard_name', 'web')),
            ],
            'description' => ['required', 'max:125'],
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $role = DB::transaction(function () use ($request, $tenantId) {
            $role = Role::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'guard_name' => 'web',
                'tenant_id' => $tenantId,
            ]);

            $role->syncPermissions(Permission::query()->whereIn('id', $request->input('permission_ids', []))->get());

            return $role;
        });
        $this->forgetPermissionCache();

        return $this->sendResponse($role->load('permissions'), 'Role criada.', 201);
    }

    public function update(Request $request, int $id)
    {
        $role = $this->resolveRoleForStore($request, $id);

        if ($role->tenant_id === null) {
            if (! $this->userMayManageGlobalRoles($request)) {
                abort(403, 'Apenas administradores de plataforma podem alterar papéis globais.');
            }
        } elseif (! $request->user()?->can('roles_edit')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:40',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('guard_name', $role->guard_name))->ignore($role->id),
            ],
            'description' => ['required', 'max:125'],
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $role) {
            $role->fill([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ])->save();
            $role->syncPermissions(Permission::query()->whereIn('id', $request->input('permission_ids', []))->get());
        });
        $this->forgetPermissionCache();

        return $this->sendResponse($role->fresh()->load('permissions'));
    }

    public function destroy(Request $request, int $id)
    {
        $role = $this->resolveRoleForStore($request, $id);

        if ($role->tenant_id === null && ! $this->userMayManageGlobalRoles($request)) {
            abort(403, 'Apenas administradores de plataforma podem remover papéis globais.');
        }
        if ($role->tenant_id !== null && ! $request->user()?->can('roles_delete')) {
            abort(403);
        }

        try {
            $role->delete();
            $this->forgetPermissionCache();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    public function syncPermissions(Request $request, int $id)
    {
        $role = $this->resolveRoleForStore($request, $id);

        if ($role->tenant_id === null) {
            if (! $this->userMayManageGlobalRoles($request)) {
                abort(403, 'Apenas administradores de plataforma podem alterar papéis globais.');
            }
        } elseif (! $request->user()?->can('roles_edit')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $ids = $request->input('permission_ids', []);
        $role->syncPermissions(Permission::query()->whereIn('id', $ids)->get());
        $this->forgetPermissionCache();

        return $this->sendResponse($role->load('permissions'));
    }
}
