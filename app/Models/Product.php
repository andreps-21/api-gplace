<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    const PRODUCT = 'P';
    const SERVICE = 'S';

    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'video', 'reference', 'origin',
        'commercial_name', 'description',
        'um_id', 'tag', 'price',
        'promotion_price', 'discount', 'spots',
        'scores', 'payment_condition',
        'weight', 'cubic_weight', 'length', 'width', 'height',
        'brand_id', 'model', 'presentation_id', 'family_id',
        'about', 'recommendation', 'benefits',
        'formula', 'application_mode', 'dosage',
        'lack', 'other_information', 'rating',
        'is_enabled', 'sync_at', 'section_id',
        'type', 'quantity', 'type_sale', 'is_grid',
        'description_reference', 'store_id', 'specification',
        'external_id'
    ];

    protected $appends = ['promotion_percent', 'score_number'];

    public function getPromotionPercentAttribute()
    {
        if ($this->price == 0) {
            return 0;
        }
        return floatval(100 - ($this->promotion_price * 100 / $this->price));
    }

    public function getScoreNumberAttribute()
    {
        if ($this->promotion_price != 0.0) {
            return floatval(($this->scores * $this->promotion_price / 100));
        }

        if ($this->price == 0.0) {
            return 0;
        }

        return floatval(($this->scores * $this->price / 100));
    }


    public static function types($option = null)
    {
        $options =  [
            Product::PRODUCT => 'Produto'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }

    public static function typeSales($option = null)
    {
        $options =  [
            1 => 'Dinheiro',
            'Pontos',
            'Pontos+Dinheiro'
        ];

        if (!$option)
            return $options;

        return $options[$option];
    }
    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product', 'product_id', 'related_product_id');
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'product_payment_method', 'product_id', 'payment_method_id');
    }

    public function variation()
    {
        return $this->belongsToMany(Variation::class);
    }

    /**
     * Get all of the productsGrid for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productsGrid(): HasMany
    {
        return $this->hasMany(Product::class, 'reference', 'reference');
    }

    /**
     * The sections that belong to the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'product_section')
            ->withTimestamps();
    }

    /**
     * Get the measurement that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class, 'um_id');
    }


    /**
     * Get the section that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Scope a query to include people information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInfo($query)
    {
        return $query->select(
            'products.*',
            'families.name as family',
            'brands.name as brand',
            'sections.name as section',
            'measurement_units.initials as um'
        )
            ->leftJoin('families', 'families.id', '=', 'products.family_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('sections', 'sections.id', '=', 'products.section_id')
            ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id');
    }

    public static function getReference()
    {

        $codigo = self::generateReference();

        return self::isVerified($codigo);
    }

    private static function isVerified($codigo)
    {
        while (self::existsCod($codigo)) {
            $codigo = self::generateReference();
        }
        return $codigo;
    }

    private static function generateReference($qtyCaraceters = 4)
    {
        //Números aleatórios
        $numbers = (((date('Ymd') / 12) * 24) + mt_rand(800, 9999));
        $numbers .= 1234567890;

        //Junta tudo
        $characters = $numbers;

        //Embaralha e pega apenas a quantidade de caracteres informada no parâmetro
        $reference = substr(str_shuffle($characters), 0, $qtyCaraceters);

        //Retorna a referenca
        return $reference;
    }

    private static function existsCod($codigo)
    {
        return Product::where('reference', $codigo)
            ->where('store_id', session('store')['id'])
            ->exists();
    }
}
