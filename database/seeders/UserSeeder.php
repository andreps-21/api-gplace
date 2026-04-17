<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use App\Support\DevAdminPassword;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Senha: #G00d# + mês (2 dígitos) + ano (4 dígitos), ex. abril de 2026 → #G00d#042026 (sempre mês/ano correntes)
        $password = DevAdminPassword::plain();

        // NIF é único na tabela: registos antigos (ex. good@admin) usam o mesmo NIF de seed.
        $person = Person::updateOrCreate(
            ['nif' => '00000000'],
            [
                'name' => 'Admin Gooding Solutions',
                'email' => 'admin@gooding.solutions',
                'formal_name' => 'Gooding Solutions',
                'phone' => '00000000',
                'street' => 'Rua',
                'zip_code' => '770000',
                'city_id' => '443',
            ]
        );

        $user = User::updateOrCreate(
            ['person_id' => $person->id],
            [
                'name' => 'Admin Gooding Solutions',
                'email' => 'admin@gooding.solutions',
                'password' => bcrypt($password),
            ]
        );

        $user->syncRoles(['administrador']);

        $this->command->info('User admin@gooding.solutions garantido (criado ou actualizado).');
        $this->command->info('Senha do seed (mês/ano correntes; hash alinhado no login API/web): ' . $password);
    }
}
