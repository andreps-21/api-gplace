<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFormTemplateField extends Model
{
    protected $fillable = [
        'product_form_template_id',
        'field_key',
        'label',
        'type',
        'is_fixed',
        'is_visible',
        'is_required',
        'show_on_ecommerce',
        'show_as_filter',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
        'show_on_ecommerce' => 'boolean',
        'show_as_filter' => 'boolean',
        'options' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductFormTemplate::class, 'product_form_template_id');
    }
}
