<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'code', 'status', 'customer_id', 'salesman_id', 'vl_amount',
        'vl_icms', 'vl_ipi', 'vl_freight', 'vl_discount', 'total',
        'payment_method_id', 'code_payment', 'delivery_place',
        'description', 'payment_condition', 'purchase_date', 'type',
        'return_payment', 'ticket_link', 'tracking_code', 'address_id',
        'vl_spots', 'voucher', 'coupon_id', 'store_id'
    ];

    public function getInfoAttribute()
    {
        return $this->code;
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the address that owns the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }
    public function payment()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function average()
    {
        return $this->belongsToMany(
            Average::class,
            'average_orders',
            'average_id',
            'order_id'
        )->withTimestamps()
            ->withPivot(['average_code']);
    }

    public static function status($option = null)
    {
        $options =  [
            '1' => 'Em Aprovação',
            '2' => 'Aprovado',
            '3' => 'Pendente',
            '4' => 'Em Faturamento',
            '5' => 'Em expedição',
            '6' => 'Despachado',
            '7' => 'Entregue',
            '8' => 'Cancelado'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }
    public static function types($option = null)
    {
        $options =  [
            '1' => 'Entrega',
            '2' => 'Retirada'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }
}
