<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Average extends Model
{
    protected $fillable = [
        'average_code'
    ];

    public function getInfoAttribute()
    {
        return $this->average_code ;
    }

}
