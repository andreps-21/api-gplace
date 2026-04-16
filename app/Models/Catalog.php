<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    protected $fillable = [
        'name', 'url', 'text_email', 'subject',
        'image', 'is_enabled', 'store_id'
    ];

}
