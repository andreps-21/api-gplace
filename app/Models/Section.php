<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class Section extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'name', 'descriptive',
        'type', 'use', 'is_enabled',
        'parent_id', 'store_id', 'order',
        'is_home', 'order_home', 'image'
    ];

    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }


    public static function types($option = null)
    {
        $options =  [
            'S' => 'Sintética',
            'A' => 'Analítica'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    /**
     * Get all of the products for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all of the products for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function auxProducts(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'product_section')
            ->withTimestamps();
    }

    /**
     * The sections that belong to the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function auxiliarProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_section')
            ->withTimestamps();
    }
}
