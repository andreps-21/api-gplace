<?php

namespace App\Actions;

use App\Actions\Pagseguro\PagseguroCreditCardPaymentAction;
use App\Enums\GatewayPayment;
use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;

class CreditCardPaymentAction
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
                $action = new PagseguroCreditCardPaymentAction();

                $result = $action->execute(
                    $order,
                    $request,
                    boolval($settings->payment_info['sandbox']),
                    $settings->payment_info['token']
                );

                return $result;
            } else if ($settings->payment_gateway == GatewayPayment::SICRED) {
                throw new Exception("Forma de pagamento não disponível para esse gateway");
            }
            return null;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
