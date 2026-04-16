<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Provider extends Model
{
    protected $fillable = [
        'state_registration', 'municipal_registration', 'type',
        'local_coordinates', 'contact', 'contact_email',
        'status', 'contact_phone',
        'own_equipment', 'own_transport', 'birth_date',
        'notes', 'profession_id', 'person_id',
        'bank_id', 'agency', 'account', 'account_type',
    ];

    public static function types($option = null)
    {
        $options =  [
            1 => 'Prestador',
            2 => 'Clinica/Prestador',
            3 => 'Fornecedor'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function opStatus($option = null)
    {
        $options =  [
            1 => 'Interessado',
            2 => 'Habilitado',
            3 => 'Bloqueado',
            4 => 'Cancelado'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function accountTypes($option = null)
    {
        $options =  [
            'C' => 'Corrente',
            'P' => 'Poupança'
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
            'providers.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.district',
            'people.zip_code',
            'people.city_id',
            DB::raw("concat(cities.title, ' - ', states.letter) as city"),
            'professions.name AS profession',
            'banks.name as bank'
        )
            ->join('people', 'people.id', '=', 'providers.person_id')
            ->join('professions', 'providers.profession_id', '=', 'professions.id')
            ->join('cities', 'people.city_id', '=', 'cities.id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->leftJoin('banks', 'banks.id', '=', 'providers.bank_id');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }
}
