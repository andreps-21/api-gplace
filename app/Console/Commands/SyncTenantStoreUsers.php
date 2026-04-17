<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\TenantStoreUserSync;
use Illuminate\Console\Command;

/**
 * Corrige dados antigos em que o contratante existia mas não tinha pivot em store_user.
 */
class SyncTenantStoreUsers extends Command
{
    protected $signature = 'gplace:sync-tenant-store-users';

    protected $description = 'Associa cada utilizador contratante às lojas do seu tenant (pivot store_user, exigido pelo middleware user_store).';

    public function handle(): int
    {
        $ids = Tenant::query()->orderBy('id')->pluck('id');
        foreach ($ids as $tid) {
            TenantStoreUserSync::attachContractorToStoresForTenant((int) $tid);
        }
        $this->info('Titulares processados: '.$ids->count().'.');

        return Command::SUCCESS;
    }
}
