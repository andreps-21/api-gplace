<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;

class SizeImage extends Model
{
    use HasFactory, EagerLoadPivotTrait;

    protected $table = 'size_images';

    protected $fillable = [
        'name','size_width','size_height','is_enabled','type', 'code'
    ];

    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    public function interfacePositions(): BelongsToMany
    {
        return $this->belongsToMany(InterfacePosition::class, 'interface_position_size_images')
            ->withTimestamps();
    }
}
