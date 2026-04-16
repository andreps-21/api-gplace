<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BusinessUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'city_id', 'is_enabled',
        'zip_code_start', 'zip_code_end', 'tenant_id'
    ];

    public function scopeCity($query)
    {
        return $query->select(
            'business_units.*',
            'cities.title',
            'states.letter',
            DB::raw("concat(cities.title, ' - ', states.letter) as city")
        )
            ->join('cities', 'cities.id', '=', 'business_units.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }

}
