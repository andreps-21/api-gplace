<?php

namespace App\Enums;

abstract class GatewayPayment
{

    const PAGSEGURO = 1;
    const SICRED = 2;

    public static function types($option = null)
    {
        $options =  [
            self::PAGSEGURO => 'Pagseguro',
            self::SICRED => 'Sicredi',
        ];

        if (!$option)
            return $options;
        return $options[$option];
    }
}
