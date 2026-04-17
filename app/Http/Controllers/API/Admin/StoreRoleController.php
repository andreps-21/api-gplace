<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Detalhe de role do tenant e sincronização de permissões Spatie (substitui fluxo Blade).
 */
class StoreRoleController extends BaseController
{
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
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $ids = $request->input('permission_ids', []);
        $role->syncPermissions(Permission::query()->whereIn('id', $ids)->get());

        return $this->sendResponse($role->load('permissions'));
    }
}
