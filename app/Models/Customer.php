<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_registration', 'origin',
        'birth_date', 'type',
        'contact', 'contact_phone', 'contact_email',
        'status', 'notes', 'sync_at',
        'person_id', 'municipal_registration',
        'external_id'
    ];

    public function getInfoAttribute()
    {
        return $this->name . ' - ' . $this->nif;
    }

    public static function types($option = null)
    {
        $options =  [
            1 => 'Normal',
            'VIP'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function origins($option = null)
    {
        $options =  [
            1 => 'E-commerce',
            'ERP'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function opStatus($option = null)
    {
        $options =  [
            1 => 'Habilitado',
            'Bloqueado',
            'Inativo'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePerson($query)
    {
        return $query->select(
            'customers.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.city_id',
            'people.zip_code',
            'people.district',
            'people.number',
            DB::raw("concat(cities.title, ' - ', states.letter) as city"),
            'states.letter as state',
            'cities.title as city_name'
        )
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }
    public function people()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class);
    }
}
