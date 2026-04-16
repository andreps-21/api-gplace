<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Freight extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'city_id', 'is_enabled', 'description',
        'zip_code_start', 'zip_code_end', 'notes','percentage', 
        'value_freight_fix','store_id', 'free_shipping_sales'
    ];
    
    public function scopeCity($query)
    {
        return $query->select(
            'freights.*',
            'cities.title',
            'states.letter',
            DB::raw("concat(cities.title, ' - ', states.letter) as city")
        )
            ->join('cities', 'cities.id', '=', 'freights.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }
}