<?php

namespace App\Actions\Maxdata;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaxDataToken
{
    public function execute($url, $terminal, $empId)
    {

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'terminal' => $terminal,
            'empId' => $empId,
        ])->get("{$url}/v1/auth");

        if ($response->failed()) {
            Log::error("Erro na geração do token max data: " . json_encode($response->body()));
            throw new Exception($response->body());
        }

        $result = $response->json();

        return $result;
    }
}
