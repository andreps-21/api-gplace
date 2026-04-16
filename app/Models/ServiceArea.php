<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceArea extends Model
{
    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'services_area';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description','is_enabled'
    ];

}
