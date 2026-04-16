<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DefenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createPermissions();
        $this->createRoles();
    }

    private function createPermissions()
    {
        $array = Permission::PERMISSIONS;

        $percorrerArray = function ($array) use (&$percorrerArray) {
            foreach ($array as $value) {
                if (is_array($value) && !array_key_exists('name', $value)) {
                    $percorrerArray($value);
                }
                if (is_array($value) && array_key_exists('name', $value)) {
                    Permission::firstOrCreate(
                        ['name' => $value["name"]],
                        $value
                    );
                }
            }
        };

        $percorrerArray($array);

        $this->command->info('Default Permissions added.');
    }

    private function createRoles()
    {
        $superadmin = Role::firstOrCreate(
            ['name' => 'administrador'],
            [
                'description' => 'Administrador'
            ]
        );

        $superadmin->permissions()->sync(Permission::all());

        $this->command->info('Superadmin will have full rights');

        Role::firstOrCreate(
            ['name' => 'contratante'],
            [
                'description' => 'Contratante'
            ]
        );
        Role::firstOrCreate(
            ['name' => 'vendedor'],
            [
                'description' => 'Vendedor',
                'guard_name' => 'web'
            ]
        );
    }
}
