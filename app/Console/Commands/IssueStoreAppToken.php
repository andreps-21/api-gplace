<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueStoreAppToken extends Command
{
    protected $signature = 'store:issue-app-token
                            {store_id? : ID na tabela `stores`}
                            {--show : Listar lojas e `app_token` actuais sem alterar}
                            {--force : Regenerar mesmo que já exista token (sem confirmação interactiva)}';

    protected $description = 'Emite ou lista o app_token da loja (header HTTP `app` / NEXT_PUBLIC_APP_TOKEN no Next.js).';

    public function handle(): int
    {
        if ($this->option('show')) {
            return $this->listStores();
        }

        $storeId = $this->argument('store_id');
        $store = $storeId !== null && $storeId !== ''
            ? Store::query()->find($storeId)
            : Store::query()->orderBy('id')->first();

        if ($storeId !== null && $storeId !== '' && !$store) {
            $this->error("Loja com id {$storeId} não encontrada.");

            return self::FAILURE;
        }

        if (!$store) {
            $this->error('Nenhuma loja na tabela stores.');
            $this->comment('Cria a primeira loja: php artisan store:bootstrap');

            return self::FAILURE;
        }

        if (filled($store->app_token) && !$this->option('force')) {
            if (!$this->confirm(
                "A loja #{$store->id} já tem app_token. Regenerar invalida o valor actual até actualizares o Vercel. Continuar?",
                false
            )) {
                $this->info('Cancelado. Usa --show para ver o token actual, ou --force para regenerar sem perguntar.');

                return self::SUCCESS;
            }
        }

        $token = Str::random(48);
        $store->app_token = $token;
        $store->save();

        $this->info("Token guardado para a loja #{$store->id}.");
        $this->newLine();
        $this->line('No deploy do frontend (Vercel), define:');
        $this->line('<fg=cyan>NEXT_PUBLIC_APP_TOKEN=' . $token . '</>');
        $this->newLine();
        $this->comment('Este valor deve coincidir com `stores.app_token` (middleware CheckAppHeader).');

        return self::SUCCESS;
    }

    private function listStores(): int
    {
        $stores = Store::query()->orderBy('id')->get(['id', 'person_id', 'tenant_id', 'app_token']);

        if ($stores->isEmpty()) {
            $this->warn('Nenhuma loja na tabela stores.');
            $this->newLine();
            $this->comment('Cria a primeira loja (produção ou qualquer ambiente): php artisan store:bootstrap');

            return self::FAILURE;
        }

        $this->table(
            ['id', 'person_id', 'tenant_id', 'app_token'],
            $stores->map(fn (Store $s) => [
                $s->id,
                $s->person_id,
                $s->tenant_id,
                $s->app_token ?: '(vazio)',
            ])->all()
        );

        $this->newLine();
        $this->comment('Para gerar um token novo: php artisan store:issue-app-token [store_id]');

        return self::SUCCESS;
    }
}
