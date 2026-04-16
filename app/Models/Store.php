<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'status',
        'person_id', 'app_token'
    ];

    public static function opStatus($option = null)
    {
        $options = [
            1 => 'Ativo',
            2 => 'Bloqueado',
            3 => 'Cancelado'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public function scopePerson($query)
    {
        return $query->select(
            'stores.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.zip_code',
            'people.city_id',
            DB::raw("concat(cities.title, ' - ', states.letter) as city")
        )
            ->join('people', 'people.id', '=', 'stores.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }

    public function people()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    /**
     * The paymentMethods that belong to the Store
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'payment_method_store');
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'store_id');
    }
}
