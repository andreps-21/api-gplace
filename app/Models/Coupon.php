<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    const PLATFORM = 'P';
    const ESTABLISHMENT = 'E';
    const DISCOUNT = 'D';
    const FREIGHT = 'F';

    protected $fillable = [
        'business_unit_id', 'name',
        'description', 'start_at',
        'end_at', 'is_enabled',
        'sponsor', 'apply',
        'discount', 'min_order',
        'quantity', 'balance', 'store_id'
    ];

    public static function sponsors($option = null)
    {
        $options = [
            self::PLATFORM => 'Parceiro',
            self::ESTABLISHMENT => 'Estabelecimento'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function applies($option = null)
    {
        $options = [
            self::FREIGHT => 'Frete Grátis',
            self::DISCOUNT => 'Produto'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    /**
     * Get the businessUnit that owns the Coupon
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

}
