<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'formal_name',
        'nif',
        'email',
        'zip_code',
        'street',
        'number',
        'city_id',
        'phone',
        'district',
        'address',
        'city_registration',
        'state_registration',
        'number'
    ];

    /**
     * Get the city that owns the Person
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the tenant associated with the Person
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }


    /**
     * Get the user associated with the Person
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
