<?php

namespace App\Enums;

abstract class SettingsStatus
{

public static function status($option = null)
    {
        $options =  [
            1 => 'Habilitado',
            'Bloqueado',
            'Suspenso',
            'Cancelado'
        ];

        if (!$option)
            return $options;
        return $options[$option];
    }

}
