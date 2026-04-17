<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/**
 * Normaliza a senha introduzida no login (API e web).
 *
 * Contratantes usam como senha inicial só os dígitos do CPF/CNPJ; ao copiar de PDF,
 * sites ou mensagens, costumam entrar marcas Unicode invisíveis (U+202A–U+202E, etc.)
 * ou formatação (pontos, barra), o que faz falhar o Hash::check.
 */
final class LoginPasswordNormalizer
{
    /**
     * Candidatos a testar com Hash::check, por ordem (do mais literal ao mais normalizado).
     *
     * @return list<string>
     */
    public static function candidates(string $plain): array
    {
        $trimmed = trim($plain);
        $stripped = preg_replace(
            '/[\x{202A}-\x{202E}\x{200E}\x{200F}\x{2066}-\x{2069}\x{FEFF}\x{200B}-\x{200D}\x{00A0}]/u',
            '',
            $trimmed
        );
        if (! is_string($stripped)) {
            $stripped = $trimmed;
        }

        $digits = preg_replace('/\D+/', '', $stripped);
        $candidates = [$trimmed, $stripped];

        // Só dígitos de CPF (11) ou CNPJ (14), quando o utilizador colou com máscara
        if ($digits !== ''
            && $digits !== $stripped
            && in_array(strlen($digits), [11, 14], true)
        ) {
            $candidates[] = $digits;
        }

        $out = [];
        foreach ($candidates as $c) {
            if ($c !== '') {
                $out[] = $c;
            }
        }

        return array_values(array_unique($out, SORT_STRING));
    }

    public static function matchesHash(string $plain, string $hashedPassword): bool
    {
        foreach (self::candidates($plain) as $candidate) {
            if (Hash::check($candidate, $hashedPassword)) {
                return true;
            }
        }

        return false;
    }
}
