<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'cities_create' => 'Criar',
            'cities_edit' => 'Editar',
            'cities_delete' => 'Deletar',
            'states_create' => 'Criar',
            'states_edit' => 'Editar',
            'states_delete' => 'Deletar',
        ];

        $defaultNames = [
            'products_create', 'products_edit', 'products_delete',
            'sections_create', 'sections_edit', 'sections_delete',
            'grid_create', 'grid_edit', 'grid_delete',
            'brands_create', 'brands_edit', 'brands_delete',
            'measurement-units_create', 'measurement-units_edit', 'measurement-units_delete',
            'freights_create', 'freights_edit', 'freights_delete',
            'banners_create', 'banners_edit', 'banners_delete',
            'size-image_create', 'size-image_edit', 'size-image_delete',
            'interface-positions_create', 'interface-positions_edit', 'interface-positions_delete',
            'coupons_create', 'coupons_edit', 'coupons_delete',
            'businessunits_create', 'businessunits_edit', 'businessunits_delete',
            'payment-methods_create', 'payment-methods_edit', 'payment-methods_delete',
            'erp_create', 'erp_edit', 'erp_delete',
            'social-medias_create', 'social-medias_edit', 'social-medias_delete',
            'cities_create', 'cities_edit', 'cities_delete',
            'states_create', 'states_edit', 'states_delete',
            'users_create', 'users_edit', 'users_delete',
            'roles_create', 'roles_edit', 'roles_delete',
            'permissions_create', 'permissions_edit', 'permissions_delete',
            'parameters_create', 'parameters_edit', 'parameters_delete',
            'faq_create', 'faq_edit', 'faq_delete',
            'catalogs_create', 'catalogs_edit', 'catalogs_delete',
            'settings_view', 'settings_create', 'settings_delete',
            'tokens_create', 'tokens_edit', 'tokens_delete',
        ];

        foreach (array_unique([...array_keys($permissions), ...$defaultNames]) as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $permissions[$name] ?? $this->descriptionFor($name)]
            );
        }

        $ids = Permission::query()->whereIn('name', $defaultNames)->pluck('id')->all();
        if ($ids === []) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('name', 'contratante')
                    ->orWhere('name', 'like', 'contratante-%');
            })
            ->cursor()
            ->each(function (Role $role) use ($ids): void {
                $role->permissions()->syncWithoutDetaching($ids);
            });
    }

    public function down(): void
    {
        // Permissões são dados de autorização compartilhados; reversão manual se necessário.
    }

    private function descriptionFor(string $name): string
    {
        return match (substr($name, strrpos($name, '_') + 1)) {
            'view' => 'Visualizar',
            'create' => 'Criar',
            'edit' => 'Editar',
            'delete' => 'Deletar',
            default => 'Permissão',
        };
    }
};
