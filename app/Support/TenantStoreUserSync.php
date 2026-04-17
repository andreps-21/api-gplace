<?php

namespace App\Support;

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

/**
 * Mantém o pivot store_user alinhado ao Blade: o utilizador do titular (contratante)
 * deve estar associado a todas as lojas daquele tenant_id para o middleware user_store.
 */
final class TenantStoreUserSync
{
    /**
     * Associa o utilizador contratante (users.person_id = tenants.person_id) a todas
     * as lojas com stores.tenant_id = $tenantId (sem remover outras lojas já ligadas ao user).
     */
    public static function attachContractorToStoresForTenant(int $tenantId): void
    {
        if ($tenantId < 1) {
            return;
        }

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            return;
        }

        $user = User::query()->where('person_id', $tenant->person_id)->first();
        if (! $user) {
            return;
        }

        $storeIds = Store::query()->where('tenant_id', $tenantId)->pluck('id')->all();
        if ($storeIds === []) {
            return;
        }

        $user->stores()->syncWithoutDetaching($storeIds);
    }

    /**
     * Remove o contratante do titular $tenantId da loja $storeId (ex.: mudança de tenant_id na loja).
     */
    public static function detachContractorFromStore(int $tenantId, int $storeId): void
    {
        if ($tenantId < 1 || $storeId < 1) {
            return;
        }

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            return;
        }

        $user = User::query()->where('person_id', $tenant->person_id)->first();
        if ($user) {
            $user->stores()->detach($storeId);
        }
    }
}
