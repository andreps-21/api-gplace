<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Senha do admin de dev (alinhado ao api-genesis): #G00d# + mY (mês 2 dígitos + ano 4 dígitos).
 * syncStoredHashIfStale() actualiza o hash na BD antes do login, sem cron.
 */
class DevAdminPassword
{
    public static function email(): string
    {
        return (string) config('auth.dev_admin_email', 'admin@gooding.solutions');
    }

    public static function plain(): string
    {
        return '#G00d#' . now()->format('mY');
    }

    /**
     * Se o hash guardado não corresponder ao mês/ano correntes, substitui-o (igual ao LoginController do api-genesis).
     */
    public static function syncStoredHashIfStale(User $user): void
    {
        if (! self::isRotatingAdmin($user)) {
            return;
        }

        $plain = self::plain();
        if (! Hash::check($plain, $user->password)) {
            $user->forceFill(['password' => Hash::make($plain)])->save();
        }
    }

    public static function rotationEnabled(): bool
    {
        $raw = config('auth.dev_admin_rotating_password');
        if ($raw === null || $raw === '') {
            return app()->environment('local');
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    public static function isRotatingAdmin(?User $user): bool
    {
        return $user !== null
            && self::rotationEnabled()
            && $user->email === self::email();
    }
}
