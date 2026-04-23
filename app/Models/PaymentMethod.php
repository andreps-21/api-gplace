<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class PaymentMethod extends Model
{
    const TICKET = 'Boleto';
    const CREDIT_CARD = 'CreditCard';
    const DEBIT_CARD = 'DebitCard';
    const PIX = 'PIX';
    /** Dinheiro / numerário (PDV, venda rápida). */
    const CASH = 'Cash';

    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icon', 'code',
        'description', 'is_enabled'
    ];

    public function getInfoAttribute()
    {
        return $this->description;
    }

    /**
     * The stores that belong to the PaymentMethod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'payment_method_store');
    }
}
