<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/**
 * O Blade e a API replicam permissões a partir do papel modelo `contratante`.
 * Se esse papel estiver sem permissões, o titular fica sem itens no menu (Spatie vazio).
 * Atribui um conjunto inicial de permissões de visualização alinhado ao sidebar Blade.
 */
return new class extends Migration
{
    public function up(): void
    {
        $role = Role::query()
            ->where('name', 'contratante')
            ->where('guard_name', 'web')
            ->first();

        if (! $role || $role->permissions()->count() > 0) {
            return;
        }

        $names = [
            'customers_view', 'leads_view', 'stores_view', 'salesman_view',
            'products_view', 'products_create', 'products_edit', 'sections_view', 'measurement-units_view', 'grid_view', 'brands_view',
            'freights_view', 'banners_view', 'size-image_view', 'interface-positions_view',
            'coupons_view', 'businessunits_view', 'payment-methods_view', 'erp_view',
            'cities_view', 'states_view', 'social-medias_view',
            'orders_view', 'product_report_view', 'order_report_view',
            'users_view', 'roles_view', 'permissions_view',
            'parameters_view', 'catalogs_view', 'settings_edit', 'faq_view', 'tokens_view',
            'tenants_view',
        ];

        $ids = Permission::query()->whereIn('name', $names)->pluck('id')->all();
        if ($ids === []) {
            return;
        }

        $role->permissions()->sync($ids);

        // Réplicas criadas quando o modelo estava vazio ficaram sem permissões — alinhar ao modelo.
        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'contratante-%')
            ->where('id', '!=', $role->id)
            ->cursor()
            ->each(function (Role $r) use ($ids): void {
                if ($r->permissions()->count() === 0) {
                    $r->permissions()->sync($ids);
                }
            });
    }

    public function down(): void
    {
        // Mantém permissões — reversão manual se necessário.
    }
};
