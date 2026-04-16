<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Atribuições (roles) — alinhado ao Blade roles.index com filtro por tenant da loja.
 */
class StoreRoleListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $store = $request->attributes->get('store');
        $storeId = (int) $store['id'];
        $tenantId = Store::query()->whereKey($storeId)->value('tenant_id');
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Role::query()
            ->select(
                'roles.*',
                DB::raw('case when roles.tenant_id is null then roles.description else concat(people.formal_name, " - ", roles.description) end as description')
            )
            ->leftJoin('tenants', 'tenants.id', '=', 'roles.tenant_id')
            ->leftJoin('people', 'people.id', '=', 'tenants.person_id')
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where(function ($q2) use ($tenantId) {
                    $q2->where('roles.tenant_id', $tenantId)->orWhereNull('roles.tenant_id');
                });
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($quer) use ($s) {
                    $quer->where('roles.name', 'LIKE', "%{$s}%")
                        ->orWhere('roles.description', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('description');

        return $this->sendResponse($query->paginate($perPage));
    }
}
