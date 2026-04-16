<?php

namespace App\Actions;

use App\Actions\Sicredi\SicrediGetToken;
use App\Actions\Pagseguro\PagseguroTicketPaymentAction;
use App\Actions\Sicredi\SicrediTicketPaymentAction;
use App\Enums\GatewayPayment;
use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;

class TicketPaymentAction
{
    public function execute(int $storeId, Order $order, Request $request)
    {
        $settings = Setting::where('store_id', $storeId)->first();

        if (!$settings) {
            throw new Exception("Configuração da loja não cadastrada.");
        }

        if (!$settings->payment_info) {
            throw new Exception("Pagamento não configurado para essa loja");
        }

        try {
            if ($settings->payment_gateway == GatewayPayment::PAGSEGURO) {
                $action = new PagseguroTicketPaymentAction();

                $result = $action->execute(
                    $order,
                    $request,
                    boolval($settings->payment_info['sandbox']),
                    $settings->payment_info['token']
                );

                return $result;
            } else if ($settings->payment_gateway == GatewayPayment::SICRED) {
                $tokenAction = new SicrediGetToken();
                $action = new SicrediTicketPaymentAction();
                $token = "";

                if (
                    !isset($settings->payment_info['access_token']) ||
                    !isset($settings->payment_info['token_expiration_date']) ||
                    (
                        isset($settings->payment_info['token_expiration_date']) &&
                        carbon($settings->payment_info['token_expiration_date']) < now()
                    )
                ) {
                    $response = $tokenAction->execute(
                        boolval($settings->payment_info['sandbox']),
                        $settings->payment_info['token_sicredi'],
                        $settings->payment_info['username'],
                        $settings->payment_info['password']
                    );

                    $paymentInfo = $settings->payment_info;

                    $paymentInfo['access_token'] = $response['access_token'];
                    $paymentInfo['refresh_token'] = $response['refresh_token'];
                    $paymentInfo['token_expiration_date'] = now()->addSeconds($response['expires_in']);

                    $settings->payment_info = $paymentInfo;
                    $settings->save();

                    $token = $response['access_token'];
                } else if (isset($settings->payment_info['access_token'])) {
                    $token = $settings->payment_info['access_token'];
                }

                $result = $action->execute(
                    boolval($settings->payment_info['sandbox']),
                    $token,
                    $settings->payment_info['token_sicredi'],
                    $settings->payment_info['cooperative'],
                    $settings->payment_info['agency'],
                    $settings->payment_info['covenant'],
                    $order,
                    $request,
                );

                return $result;
            }
            return null;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
