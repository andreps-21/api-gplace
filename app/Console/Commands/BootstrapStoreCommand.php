<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use App\Support\FirstStoreBootstrap;
use Illuminate\Console\Command;
use InvalidArgumentException;

class BootstrapStoreCommand extends Command
{
    protected $signature = 'store:bootstrap
                            {email? : Email do utilizador a associar à primeira loja (omissão: admin@gooding.solutions)}';

    protected $description = 'Cria a primeira loja (tenant + store + app_token) quando a tabela stores está vazia. Para uso em produção sem Blade.';

    public function handle(): int
    {
        if (Store::query()->exists()) {
            $this->error('Já existem lojas. Não é necessário bootstrap.');
            $this->comment('Lista tokens: php artisan store:issue-app-token --show');

            return self::FAILURE;
        }

        $email = $this->argument('email') ?: 'admin@gooding.solutions';
        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            $this->error("Nenhum utilizador com email «{$email}».");
            $this->comment('Cria o utilizador (ex.: UserSeeder) ou passa outro email: php artisan store:bootstrap outro@email.com');

            return self::FAILURE;
        }

        try {
            $store = FirstStoreBootstrap::createStoreForUser($user);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Primeira loja criada (id ' . $store->id . ').');
        $this->newLine();
        $this->line('No Vercel / frontend:');
        $this->line('<fg=cyan>NEXT_PUBLIC_APP_TOKEN=' . $store->app_token . '</>');
        $this->newLine();
        $this->comment('Regenerar token: php artisan store:issue-app-token ' . $store->id);

        return self::SUCCESS;
    }
}
