<?php

namespace App\Actions;

use App\Models\Order;

class NotificationAction
{
    public static function execute($information)
    {
        $allStatus = [
            1 => 1,
            2 => 1,
            3 => 2,
            4 => 2,
            5 => 7,
            6 => 7,
            7 => 7
        ];


        $status = $information->getStatus()->getCode();

        $order = Order::where('code_payment', $information->getReference())
            ->first();

        if ($order) {
            $order->status = $allStatus[$status];
            $order->return_payment = $information;
            $order->save();
        }
    }
}
