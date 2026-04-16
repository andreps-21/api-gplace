<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Support\FirstStoreBootstrap;
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

        try {
            FirstStoreBootstrap::createStoreForUser($user, self::DEV_APP_TOKEN);
        } catch (\InvalidArgumentException) {
            return;
        }

        $this->command?->info('LocalDevStoreSeeder: loja de dev criada. No frontend use NEXT_PUBLIC_APP_TOKEN=' . self::DEV_APP_TOKEN);
    }
}
