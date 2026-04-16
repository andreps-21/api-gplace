<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Pivots\BannerSizeImage;

class Banner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'filename', 'is_enabled',
        'position', 'type', 'sequence',
        'time', 'url', 'store_id'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->filename);
    }

    public function getTimeAttribute($value)
    {
        return Carbon::parse('2020-01-01 ' . $value)->format('i:s');
    }


    public static function positions($option = null)
    {
        $options = [
            1 => 'Loja Web(Slide-Topo)',
            // 2 => 'Loja  web (Catálogo)',
            3 => 'Loja  web (Footer:Empresa)',
            4 =>'Loja Mobile(Slide-Topo)',
            // 5 => 'Loja Mobile(Catálogo)',
            6 => 'Loja (Tarja Destaque) ',
            7 => 'Loja Web (Centro)',
            // 8 => 'Loja Web(Parceiros)',
            9 => 'Loja Web (Full)',
            10 => 'Loja (Flutuante)'
        ];

        if (!$option)
            return $options;

        if (array_key_exists($option, $options)) {
            return $options[$option];
        }

        return null;
    }

    public static function types($option = null)
    {
        $options = [
            1 => 'Banner',
            'Url/Script',
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    /**
     * The sizeImages that belong to the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sizeImages(): BelongsToMany
    {
        return $this->belongsToMany(SizeImage::class, 'banner_size_image')
            ->using(BannerSizeImage::class)
            ->withTimestamps()
            ->withPivot(['interface_position_id']);
    }
}
