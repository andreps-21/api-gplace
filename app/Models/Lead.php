<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'email',
        'cellphone',
        'status',
        'store_id',
        'person_id',
        'observation',
        'status',
        'site_id'
    ];

    public static function status($option = null)
    {
        $options =  [
            1 => 'Ativo',
            2 => 'Cliente',
            3 => 'Stand by'
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
            "leads.*",
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.city_id',
            'people.zip_code',
            'people.district',
            'people.city_registration',
            'people.state_registration',
            'people.address',
            'people.number',
            DB::raw("concat(cities.title, ' - ', states.letter) as city"),
            'states.letter as state',
            'cities.title as city_name'
        )
            ->join('people', 'people.id', '=', 'leads.person_id')
            ->leftJoin('cities', 'cities.id', '=', 'people.city_id')
            ->leftJoin('states', 'states.id', '=', 'cities.state_id');
    }

    public function people()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function store(){
        return $this->belongsTo(Store::class);
    }
    
}
