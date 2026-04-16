<?php

namespace App\Actions;

use App\Models\BusinessUnit;
use App\Models\Coupon;
use Exception;

class ValidateCoupon
{

    public function execute($coupon, $totalOrder)
    {
        $coupon = Coupon::where('name', $coupon)->first();

        if (!$coupon) {
            throw new Exception("Cupom não encontrado.");
        }

        if (!$coupon->is_enabled) {
            throw new Exception("Cupom não está mais ativo.");
        }

        if ($coupon->start_at > now() || $coupon->end_at < now()) {
            throw new Exception("Cupom vencido.");
        }

        if($coupon->balance <= 0){
            throw new Exception("Cupom atingiu a quantidade de uso.");
        }

        if($totalOrder < $coupon->min_order){
            throw new Exception("Para usar o cupom o pedido mínimo e de R$". floatToMoney($coupon->min_order));
        }

        return $coupon;
    }
}
