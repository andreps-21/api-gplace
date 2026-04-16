<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'zip_code',
        'street', 'number', 'complement',
        'district', 'reference', 'city_id'
    ];

    /**
     * Get the city that owns the UserAddress
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeInfo($query)
    {
        return $query->select(
            'addresses.*',
            'cities.title as city',
            'cities.state_id as state_id',
            'states.letter as state',
        )
            ->join('cities', 'cities.id', '=', 'addresses.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }
}
