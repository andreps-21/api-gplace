<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/**
 * O modelo Blade exige `products_create` para cadastrar produtos; o SPA admin só precisava de `products_view`
 * na listagem, mas o titular contratante deve poder criar/editar na própria loja.
 * Réplicas `contratante-*` herdam o mesmo conjunto.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permIds = Permission::query()
            ->whereIn('name', ['products_create', 'products_edit'])
            ->pluck('id')
            ->all();

        if ($permIds === []) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q): void {
                $q->where('name', 'contratante')->orWhere('name', 'like', 'contratante-%');
            })
            ->cursor()
            ->each(function (Role $role) use ($permIds): void {
                $existing = $role->permissions()->pluck('permissions.id')->all();
                $role->permissions()->sync(array_values(array_unique(array_merge($existing, $permIds))));
            });
    }

    public function down(): void
    {
        $permIds = Permission::query()
            ->whereIn('name', ['products_create', 'products_edit'])
            ->pluck('id')
            ->all();

        if ($permIds === []) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q): void {
                $q->where('name', 'contratante')->orWhere('name', 'like', 'contratante-%');
            })
            ->cursor()
            ->each(function (Role $role) use ($permIds): void {
                $role->permissions()->detach($permIds);
            });
    }
};
