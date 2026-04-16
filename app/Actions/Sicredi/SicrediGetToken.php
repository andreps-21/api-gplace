<?php

namespace App\Actions\Sicredi;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SicrediGetToken
{
    public function execute(bool $isSandbox, $token, $username, $password)
    {

        $url = config('sicredi.host.production');

        if ($isSandbox) {
            $url = config('sicredi.host.sandbox');
        }

        $response = Http::asForm()->withHeaders([
            'Accept' => 'application/json',
            'context' => 'COBRANCA',
            'x-api-key' => $token,
        ])->post("{$url}/auth/openapi/token", [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'scope' => 'cobranca',
        ]);

        $result = $response->json();

        if ($response->failed()) {
            Log::error("Erro na geração do token sicredi: " . json_encode($response->body()));
            if (isset($result) && isset($result['error_description'])) {
                throw new Exception("SICREDI: " . $result['error_description']);
            }
            throw new Exception("Erro na geração do token sicredi");
        }


        return $result;
    }
}
