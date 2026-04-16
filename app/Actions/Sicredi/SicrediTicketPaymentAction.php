<?php

namespace App\Actions\Sicredi;

use App\Models\City;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SicrediTicketPaymentAction
{
    public function execute(
        bool $isSandbox,
        string $token,
        string $apiKey,
        string $cooperative,
        string $agency,
        string $covenant,
        Order $order,
        Request $request
    ) {
        $url = config('sicredi.host.production');

        if ($isSandbox) {
            $url = config('sicredi.host.sandbox');
        }

        $city = City::with('state')->find($request->address['city']);

        $data = [
            'tipoCobranca' => 'HIBRIDO',
            'codigoBeneficiario' => $covenant,
            'pagador' => [
                'tipoPessoa' => strlen($request->customer['nif']) == 11 ? 'PESSOA_FISICA' : 'PESSOA_JURIDICA',
                'documento' => $request->customer['nif'],
                'nome' => $request->customer['name'],
                'endereco' => $request->address['street'],
                'cidade' => $city->title,
                'uf' => $city->state->letter,
                'cep' => $request->address['zip_code'],
            ],
            'especieDocumento' => 'DUPLICATA_MERCANTIL_INDICACAO',
            'seuNumero' => str_pad($order->id, 10, '0', STR_PAD_LEFT),
            'dataVencimento' => today()->addDays(5)->format('Y-m-d'),
            'valor' => $order->total,
        ];

        $response = Http::asJson()
            ->withHeaders([
                'Authorization' => "Bearer " . $token,
                'Accept' => 'application/json',
                'x-api-key' => $apiKey,
                'cooperativa' => $cooperative,
                'posto' => $agency,
            ])->post(
                "{$url}/cobranca/boleto/v1/boletos",
                $data
            );

        $checkout = $response->json();

        if ($response->failed()) {
            Log::error("Erro criação boleto sicredi: " . json_encode($response->body()));
            if (isset($checkout) && isset($checkout['message'])) {
                throw new Exception($checkout['message']);
            }
            throw new Exception("Erro criação boleto sicredi");
        }

        $response = Http::asJson()
            ->withHeaders([
                'Authorization' => "Bearer " . $token,
                'x-api-key' => $apiKey,
            ])->get("{$url}/cobranca/boleto/v1/boletos/pdf?linhaDigitavel=" . $checkout['linhaDigitavel']);


        if ($response->failed()) {
            Log::error("Erro criação boleto sicredi: " . json_encode($response->body()));
            if (isset($checkout) && isset($checkout['message'])) {
                throw new Exception($checkout['message']);
            }
            throw new Exception("Erro criação boleto sicredi");
        }

        $binary = $response->body();
        $filename = uniqid() . '.pdf';
        Storage::disk("public")->put("/tickets/$filename", $binary);
        $ticketUrl = asset('storage/tickets/' . $filename);

        $result = [
            'id' => $checkout['txid'],
            'ticket_url' => $ticketUrl,
            'status' => 'PENDING',
            'created_at' => now(),
            'payment_response' => $checkout,
            'payed' => false
        ];

        return $result;
    }
}
