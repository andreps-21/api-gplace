<?php
namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Parameter::firstOrCreate(
            ['name' => 'CEPORIGEM'],
            [
                'type' => 2,
                'value' => '77001004',
                'description' => 'CEP de Origem dos produtos',
            ]
        );
    }
}
