<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante cabeçalhos CORS em respostas que não passam pelo fim do pipeline
 * (ex.: exceções → o browser mostra "CORS" em vez do erro real).
 */
class CorsResponseHeaders
{
    public static function applyIfAllowed(Request $request, Response $response): void
    {
        if ($response->headers->has('Access-Control-Allow-Origin')) {
            return;
        }

        $origin = $request->headers->get('Origin');
        if (! $origin || ! is_string($origin)) {
            return;
        }

        $origins = config('cors.allowed_origins', []);
        $patterns = config('cors.allowed_origins_patterns', []);

        if (in_array('*', $origins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');

            return;
        }

        if (in_array($origin, $origins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin', false);

            return;
        }

        foreach ($patterns as $pattern) {
            if ($pattern !== '' && @preg_match((string) $pattern, $origin)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Vary', 'Origin', false);

                return;
            }
        }
    }
}
