<?php

use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = Role::query()->with('permissions')->where('name', '=', 'contratante')->first();
        $tenants = Tenant::query()->get();

        foreach ($tenants as $tenant) {
            $user = $tenant->people->user;
            $newRole = $role->replicate();
            $newRole->created_at = now();
            $newRole->updated_at = now();
            $newRole->name = "contratante-" . Str::slug($tenant->people->name);
            $newRole->save();
            $newRole->permissions()->sync($role->permissions);
            $user->roles()->detach();
            $user->roles()->attach($newRole->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
