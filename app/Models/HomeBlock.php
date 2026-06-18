<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeBlock extends Model
{
    public const TYPE_CATEGORIES = 'categories';
    public const TYPE_PRODUCTS = 'products';
    public const TYPE_BRANDS = 'brands';
    public const TYPE_BANNERS = 'banners';

    protected $fillable = [
        'store_id',
        'title',
        'type',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_CATEGORIES,
            self::TYPE_PRODUCTS,
            self::TYPE_BRANDS,
            self::TYPE_BANNERS,
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomeBlockItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
