<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Salesman extends Model
{

    protected $fillable = [
        'person_id', 'state_registration', 'municipal_registration',
        'status', 'description', 'birth_date'
    ];

    public function getInfoAttribute() {
        return $this->people->name . '-' . $this->people->nif;
    }

    public function people() {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public static function opStatus($option = null){

        $options = [
            '1' => 'Habilitado',
            '2' => 'Bloqueado',
            '3' => 'Inativo'
        ];

        if(!$option)
            return $options;

        return $options[$option];
    }

    public function scopePerson($query)
    {
        return $query->select(
            'salesmen.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.city_id',
            'people.zip_code',
            DB::raw("concat(cities.title, ' - ', states.letter) as city")
        )
            ->join('people', 'people.id', '=', 'salesmen.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }
    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

}
