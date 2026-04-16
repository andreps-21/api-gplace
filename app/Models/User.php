<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'person_id',
        'is_enabled'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePerson($query)
    {
        return $query->select(
            'users.*',
            'people.name',
            'people.formal_name',
            'people.phone',
            'people.nif',
            'people.street',
            'people.number',
            'people.zip_code',
            'people.district',
            'people.city_id',
            DB::raw("concat(cities.title, ' - ', states.letter) as city"),
            'customers.state_registration'
        )
            ->join('people', 'people.id', '=', 'users.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->leftjoin('customers', 'customers.person_id', '=', 'people.id');
    }

    /**
     * Get the people that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function people(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * The stores that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_user', 'user_id', 'store_id')
            ->withTimestamps();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
