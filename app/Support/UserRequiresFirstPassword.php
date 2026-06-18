<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UserRequiresFirstPassword
{
    /**
     * Senha inicial ainda é o NIF (dígitos) — mesmo critério do LoginController Blade.
     */
    public static function check(User $user): bool
    {
        $user->loadMissing('people');
        $nif = $user->people?->nif;
        if ($nif === null || $nif === '') {
            return false;
        }

        $digits = preg_replace('/\D+/', '', (string) $nif);

        return $digits !== '' && Hash::check($digits, (string) $user->password);
    }
}
