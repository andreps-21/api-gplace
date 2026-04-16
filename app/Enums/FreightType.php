<?php

namespace App\Enums;

abstract class FreightType
{

    const CORREIOS = 1;
    const MELHOR_ENVIO = 2;
    const OWNER = 3;

    public static function types($option = null)
    {
        $options =  [
            self::CORREIOS => 'Correios',
            self::MELHOR_ENVIO => 'Melhor Envio',
            self::OWNER => 'Próprio',
        ];

        if (!$option)
            return $options;
        return $options[$option];
    }
}
