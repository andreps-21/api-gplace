<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfacePosition extends Model
{
    use HasFactory;

    protected $table = 'interface_positions';

    protected $fillable = [
        'id_position', 'position_name', 'is_enabled'
    ];

    public function getInstance()
    {
        $instance = InterfacePosition::latest()->first();
        return str_pad($instance instanceof InterfacePosition ? $instance->id_position + 1 : 1, 3, 0, STR_PAD_LEFT);
    }
}
