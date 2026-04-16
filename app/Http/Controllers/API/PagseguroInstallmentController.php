<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PagseguroInstallmentController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $settings = Setting::where('store_id', $request->get('store')['id'])->first();

        if (!$settings) {
            return $this->sendError("Configuração da loja não cadastrada.", [], 403);
        }

        if (!$settings->payment_info) {
            return $this->sendError("Pagamento não configurado para essa loja", [], 403);
        }

        $isSandbox = boolval($settings->payment_info['sandbox']);

        $url = config('laravelpagseguro.host.production');

        if ($isSandbox) {
            $url = config('laravelpagseguro.host.sandbox');
        }


        $value = intval(
            strval(floatval(
                preg_replace("/[^0-9.]/", "", str_replace(',', '.', $request->value))
            ) * 100)
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $settings->payment_info['token'],
            'x-api-version' => '4.0'
        ])->get(
            "{$url}/charges/fees/calculate?payment_methods=credit_card&value=" . $value,
        );

        if ($response->failed()) {
            return $this->sendError($response->json(), [], 403);
        }

        $result = $response->json();

        return $this->sendResponse($result);
    }
}
