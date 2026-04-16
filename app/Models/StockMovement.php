<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_ADMIN_CREATE = 'admin_create';

    public const TYPE_ADMIN_ADJUST = 'admin_adjust';

    public const TYPE_ORDER_SALE = 'order_sale';

    public const TYPE_LOT_RECEIPT = 'lot_receipt';

    protected $fillable = [
        'store_id',
        'product_id',
        'user_id',
        'order_id',
        'stock_lot_id',
        'movement_type',
        'quantity_delta',
        'balance_after',
        'note',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function stockLot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class);
    }
}
