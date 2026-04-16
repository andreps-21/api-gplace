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

    public function show(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $role = Role::query()
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->with('permissions')
            ->firstOrFail();

        return $this->sendResponse([
            'role' => $role,
            'permission_ids' => $role->permissions->pluck('id')->values()->all(),
        ]);
    }

    public function syncPermissions(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $role = Role::query()
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

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
