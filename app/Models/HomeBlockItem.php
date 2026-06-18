<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeBlockItem extends Model
{
    protected $fillable = [
        'home_block_id',
        'item_id',
        'sort_order',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(HomeBlock::class, 'home_block_id');
    }
}
