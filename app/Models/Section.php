<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class Section extends Model
{
    use HasFactory, NodeTrait;

    /**
     * Laravel 9 não tem {@see Model::whenBooted()}; versões recentes de `kalnoy/nestedset` usam-no em
     * `NodeTrait::bootNodeTrait()` e o modelo deixa de arrancar. Registamos os mesmos eventos sem deferral.
     */
    public static function bootNodeTrait(): void
    {
        static::saving(function ($model) {
            return $model->callPendingAction();
        });

        static::deleting(function ($model) {
            $model->refreshNode();
        });

        static::deleted(function ($model) {
            $model->deleteDescendants();
        });

        if (static::usesSoftDelete()) {
            static::restoring(function ($model) {
                static::$deletedAt = $model->{$model->getDeletedAtColumn()};
            });

            static::restored(function ($model) {
                $model->restoreDescendants(static::$deletedAt);
            });
        }
    }

    protected $fillable = [
        'name', 'descriptive',
        'type', 'use', 'is_enabled',
        'parent_id', 'store_id', 'order',
        'is_home', 'order_home', 'image'
    ];

    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }


    public static function types($option = null)
    {
        $options =  [
            'S' => 'Sintética',
            'A' => 'Analítica'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    /**
     * Get all of the products for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all of the products for the Section
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function auxProducts(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'product_section')
            ->withTimestamps();
    }

    /**
     * The sections that belong to the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function auxiliarProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_section')
            ->withTimestamps();
    }
}
