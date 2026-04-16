<?php

namespace App\Pivots;

use App\Models\InterfacePosition;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BannerSizeImage extends Pivot
{
    /**
     * Get the user that owns the BannerSizeImage
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function interfacePosition(): BelongsTo
    {
        return $this->belongsTo(InterfacePosition::class, 'interface_position_id');
    }
}
