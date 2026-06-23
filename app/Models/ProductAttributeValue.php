<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'store_product_field_setting_id',
        'field_key',
        'value',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fieldSetting(): BelongsTo
    {
        return $this->belongsTo(StoreProductFieldSetting::class, 'store_product_field_setting_id');
    }
}
