<?php

namespace App\Support;

use App\Models\PaymentMethod;
use App\Models\Person;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FirstStoreBootstrap
{
    /**
     * Cria tenant + loja + vínculo ao utilizador e métodos de pagamento activos (mesma lógica do LocalDevStoreSeeder).
     *
     * @throws InvalidArgumentException Se o utilizador não tiver person associada
     */
    public static function createStoreForUser(User $user, ?string $appToken = null): Store
    {
        $person = Person::query()->find($user->person_id);
        if (!$person) {
            throw new InvalidArgumentException('O utilizador não tem person_id válido.');
        }

        $appToken = $appToken ?? Str::random(48);

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
            'app_token' => $appToken,
        ]);

        $user->stores()->syncWithoutDetaching([$store->id]);

        $paymentIds = PaymentMethod::query()->where('is_enabled', true)->pluck('id');
        if ($paymentIds->isNotEmpty()) {
            $store->paymentMethods()->syncWithoutDetaching($paymentIds->all());
        }

        return $store;
    }
}
