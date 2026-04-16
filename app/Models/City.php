<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory;

    public function state() {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Scope a query to include state information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStateName($query)
    {
        return $query->select(
            'cities.*',
            'states.title as state',
            'states.letter',
        )
            ->join('states', 'states.id', '=', 'cities.state_id');
    }
}
