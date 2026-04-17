<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Corrige desalinhamento histórico: `people` (titular) atualizado mas `users` ainda com email/nome antigos.
 * A partir do fix no TenantAdminController, regravar o contratante no admin também alinha estes campos.
 */
class SyncTenantUserCredentials extends Command
{
    protected $signature = 'gplace:sync-tenant-user-credentials
                            {--dry-run : Apenas listar diferenças, sem gravar}
                            {--revoke-tokens : Após cada alteração, revogar tokens Sanctum desse utilizador}';

    protected $description = 'Alinha name/email de `users` com `people` para utilizadores ligados a titulares (tenants.person_id).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $revoke = (bool) $this->option('revoke-tokens');

        $personIds = Tenant::query()->pluck('person_id')->unique()->filter()->values();
        if ($personIds->isEmpty()) {
            $this->warn('Nenhum tenant encontrado.');

            return Command::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;
        $conflicts = 0;

        User::query()
            ->whereIn('person_id', $personIds)
            ->with('people')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($dryRun, $revoke, &$updated, &$skipped, &$conflicts) {
                foreach ($users as $user) {
                    $person = $user->people;
                    if (! $person) {
                        $this->line("user_id={$user->id}: sem pessoa associada, ignorado.");
                        $skipped++;

                        continue;
                    }

                    $nameP = trim((string) $person->name);
                    $emailP = trim((string) $person->email);
                    $nameU = trim((string) $user->name);
                    $emailU = trim((string) $user->email);

                    if ($nameP === $nameU && strcasecmp($emailP, $emailU) === 0) {
                        continue;
                    }

                    $emailTaken = User::query()
                        ->where('id', '!=', $user->id)
                        ->whereRaw('LOWER(TRIM(email)) = ?', [Str::lower($emailP)])
                        ->exists();

                    if ($emailTaken) {
                        $this->error("user_id={$user->id} tenant person_id={$person->id}: email «{$emailP}» já está em uso por outro utilizador.");
                        $conflicts++;

                        continue;
                    }

                    $this->line(sprintf(
                        'user_id=%d: name «%s» → «%s» | email «%s» → «%s»',
                        $user->id,
                        $nameU,
                        $nameP,
                        $emailU,
                        $emailP
                    ));

                    if ($dryRun) {
                        continue;
                    }

                    DB::transaction(function () use ($user, $nameP, $emailP, $revoke) {
                        $user->name = $nameP;
                        $user->email = $emailP;
                        $user->save();
                        if ($revoke) {
                            $user->tokens()->delete();
                        }
                    });
                    $updated++;
                }
            });

        if ($dryRun) {
            $this->info('Modo dry-run: nada foi gravado. Correr sem --dry-run para aplicar.');
        } else {
            $this->info("Utilizadores atualizados: {$updated}. Ignorados: {$skipped}. Conflitos de email: {$conflicts}.");
        }

        return $conflicts > 0 && ! $dryRun ? Command::FAILURE : Command::SUCCESS;
    }
}
