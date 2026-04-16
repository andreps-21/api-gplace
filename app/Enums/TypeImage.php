<?php

namespace App\Enums;

abstract class TypeImage
{
    const MEDIA = 1;

    public static function types($option = null)
    {
        $options =  [
            self::MEDIA => 'Mídia',
        ];

        if (!$option)
            return $options;
        return $options[$option];
    }
}
