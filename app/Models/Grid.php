<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Grid extends Model
{
    protected $table = 'grids';

    protected $fillable = ['grid','description','is_enabled', 'type'];

    public function variation()
    {
        return $this->hasMany('App\Models\Variation');
    }

}
