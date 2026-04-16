<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'code', 'product_id', 'um', 'order_id',
        'icms', 'ipi', 'total', 'discount', 'quantity',
        'value_unit', 'spots'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
