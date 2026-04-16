<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use laravel\pagseguro\Platform\Laravel5\PagSeguro;

class GetInstallmentsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $amount = $request->amount;

        if (empty($amount)) {
            return $this->sendError('Favor informar o valor do pedido', [], 403);
        }

        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'description' => $this->getDescription(
                    $i,
                    $amount / $i,
                    $amount,
                    true
                ),
                'quantity' => $i,
                'total_amount' => $amount,
                'installment_amount' => $amount / $i,
                'interest_free' => true
            ];
        }

        return $this->sendResponse($data);
    }


    private function getDescription(
        $quantity,
        $installmentAmount,
        $totalAmount,
        $interestFree
    ) {
        $installmentAmount = floatToMoney($installmentAmount);
        $totalAmount = floatToMoney($totalAmount);
        $interestFree = $interestFree ? 'Sem Juros' : 'Com Juros';
        return "{$quantity} x {$installmentAmount} = {$totalAmount} {$interestFree}";
    }
}
