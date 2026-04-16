<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PagseguroSessionController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $settings = Setting::where('store_id',  $request->get('store')['id'])->first();

        if (!$settings) {
            return $this->sendError("Configuração da loja não cadastrada.", [], 403);
        }

        if (!$settings->payment_info) {
            return $this->sendError("Pagamento não configurado para essa loja.", [], 403);
        }

        try {
            $isSandbox = boolval($settings->payment_info['sandbox']);

            $url = "https://ws.pagseguro.uol.com.br";

            if ($isSandbox) {
                $url = "https://ws.sandbox.pagseguro.uol.com.br";
            }

            $email = $settings->payment_info['email'];
            $token =$settings->payment_info['token'];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .$settings->payment_info['token'],
                'x-api-version' => '4.0'
            ])->post(
                "{$url}/sessions?email={$email}&token={$token}",
                []
            );

            if ($response->failed()) {
                return $this->sendError($response->body(), [], 403);
            }

            $result = simplexml_load_string($response->body());

            return $this->sendResponse($result);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }
}
