<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','is_enabled', 'image', 'is_public', 'tenant_id'
    ];

    public function getImageUrlAttribute()
    {
        return asset("storage/{$this->image}");
    }


    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

}
