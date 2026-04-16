<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Person;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Cria tenant + loja + ligação ao utilizador admin para desenvolvimento local / Docker.
 * Token fixo para o header `app` do frontend (NEXT_PUBLIC_APP_TOKEN).
 */
class LocalDevStoreSeeder extends Seeder
{
    public const DEV_APP_TOKEN = 'gplace-local-frontend';

    public function run(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        if (Store::query()->where('app_token', self::DEV_APP_TOKEN)->exists()) {
            $this->command?->info('LocalDevStoreSeeder: loja com token de dev já existe — ignorado.');

            return;
        }

        $user = User::query()->where('email', 'admin@gooding.solutions')->first();
        if (!$user) {
            $this->command?->warn('LocalDevStoreSeeder: não existe admin@gooding.solutions — corre UserSeeder primeiro.');

            return;
        }

        $person = Person::query()->find($user->person_id);
        if (!$person) {
            return;
        }

        $tenant = Tenant::query()->firstOrCreate(
            ['person_id' => $person->id],
            [
                'contact' => $person->email,
                'contact_phone' => $person->phone ?? '00000000000',
                'cellphone' => null,
                'dt_accession' => Carbon::today(),
                'value' => 0,
                'signature' => 0,
                'status' => 1,
                'due_day' => 10,
                'due_date' => Carbon::today()->addYear()->format('Y-m-d'),
            ]
        );

        $store = Store::query()->create([
            'person_id' => $person->id,
            'tenant_id' => $tenant->id,
            'status' => '1',
            'app_token' => self::DEV_APP_TOKEN,
        ]);

        $user->stores()->syncWithoutDetaching([$store->id]);

        $paymentIds = PaymentMethod::query()->where('is_enabled', true)->pluck('id');
        if ($paymentIds->isNotEmpty()) {
            $store->paymentMethods()->syncWithoutDetaching($paymentIds->all());
        }

        $this->command?->info('LocalDevStoreSeeder: loja de dev criada. No frontend use NEXT_PUBLIC_APP_TOKEN=' . self::DEV_APP_TOKEN);
    }
}
