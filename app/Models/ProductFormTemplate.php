<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(ProductFormTemplateField::class)->orderBy('sort_order')->orderBy('id');
    }
}
