<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id', 'contact', 'contact_phone',
        'dt_accession', 'value',
        'status', 'signature', 'due_date', 'cellphone',
        'due_day'
    ];

    protected $dates = ['dt_accession', 'due_date'];

    public function getInfoAttribute()
    {
        return $this->people->nif . ' - ' . $this->people->name;
    }

    public function people()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public static function opStatus($option = null)
    {
        $options = [
            '1' => 'Habilitado',
            '2' => 'Inadimplente',
            '3' => 'Suspenso',
            '4' => 'Cancelado'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function opSignatures($option = null)
    {
        $options = [
            '1' => 'Trial',
            '2' => 'Mensal',
            '3' => 'Trimestral',
            '4' => 'Semestral',
            '5' => 'Anual',
            '6' => 'Bianal'

        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function opDueDays($option = null)
    {
        $options =  [
            5 => '5',
            10 => '10',
            15 => '15'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

     public static function opMonth($option = null)
    {
        $options =  [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public function scopePerson($query)
    {
        return $query->select(
            'tenants.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.email',
            'people.street',
            'people.number',
            'people.zip_code',
            'people.city_id',
            DB::raw("concat(cities.title, ' - ', states.letter) as city")
        )
            ->join('people', 'people.id', '=', 'tenants.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function sizeImages(): BelongsToMany
    {
        return $this->belongsToMany(SizeImage::class, 'size_image_tenant')
            ->withPivot(['is_enabled', 'created_at']);
    }
}
