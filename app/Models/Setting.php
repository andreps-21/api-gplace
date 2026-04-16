<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Setting extends Model
{
    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'logo', 'nif', 'full_name', 'name',
        'email', 'phone', 'maps', 'contact',
        'city_id', 'zip_code', 'address',
        'district', 'number', 'instagram_url',
        'instagram_user', 'instagram_password',
        'facebook_url', 'facebook_user',
        'facebook_password', 'youtube_url',
        'youtube_user', 'youtube_password',
        'twitter_url', 'twitter_user',
        'twitter_password', 'status', 'pixels',
        'ads', 'meta_tags', 'footer', 'terms',
        'privacy_policy', 'note', 'store_id',
        'payment_gateway', 'payment_info',
        'freight_gateway', 'freight_info',
        'portal_url', 'stamps',
        'email_notification', 'logo_footer',
        'pix_info', 'pix_gateway',
        'integration_info',
        'whatsapp_phone', 'cookies',
        'android_ver', 'apple_ver',
        'android_url_store', 'apple_url_store'
    ];

    protected $casts = [
        'payment_info' => 'array',
        'freight_info' => 'array',
        'pix_info' => 'array',
        'integration_info' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pix_info'
    ];


    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset("storage/{$this->logo}");
        }
        return $this->logo;
    }

    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function socialMedias(): BelongsToMany
    {
        return $this->belongsToMany(SocialMedia::class, 'settings_social_media', 'settings_id', 'social_media_id')
            ->withPivot([
                'user', 'password', 'url', 'token'
            ])
            ->withTimestamps();
    }

    public function erps(): BelongsToMany
    {
        return $this->belongsToMany(Erp::class, 'settings_erps', 'settings_id', 'erps_id')
            ->withPivot([
                'url', 'terminal', 'id_emp'
            ])
            ->withTimestamps();
    }
}
