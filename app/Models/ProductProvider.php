<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id', 'product_id',
        'um', 'price', 'vl_km',
        'vl_transfer', 'is_enabled'
    ];

    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInfo($query)
    {
        return $query->select(
            'product_providers.*',
            'products.commercial_name as product',
            'people.name as provider'
        )
            ->join('products', 'products.id', '=', 'product_providers.product_id')
            ->join('providers', 'providers.id', '=', 'product_providers.provider_id')
            ->join('people', 'people.id', '=', 'providers.person_id');
    }
}
