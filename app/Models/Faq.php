<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question', 'answer',
        'url', 'is_enabled', 'position',
        'store_id'
    ];

public static function positions($option = null)
    {
        $options = [
            1 => 'Portal (Catálogo - Sobre nós)',
            'Portal (Footer - Duvidas Frequentes)'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }
}
