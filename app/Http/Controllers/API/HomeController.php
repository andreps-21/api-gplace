<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\HomeBlock;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Section;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends BaseController
{
    public function __invoke(Request $request)
    {
        $storeId = $request->get('store')['id'];

        $cacheToken  = "cms-home-{$storeId}";

        $data = Cache::get($cacheToken);


        if (!$data) {

            $sections = Section::query()
                ->where('is_enabled', true)
                ->where('store_id', $storeId)
                ->orderBy('order')
                ->where(function ($query) {
                    $query->whereHas('products', fn ($query) => $query->where('is_enabled', true))
                        ->orWhereHas('children', function ($q) {
                            $q->whereHas('products', fn ($query) => $query->where('is_enabled', true))
                                ->orWhereHas('auxProducts', fn ($query) => $query->where('is_enabled', true));
                        })
                        ->orWhereHas('auxProducts', fn ($query) => $query->where('is_enabled', true));
                })
                ->orderBy('order')
                ->get([
                    'id', 'name', 'parent_id', '_lft', '_rgt',
                    'order', 'order_home', 'is_home', 'descriptive', 'image'
                ])
                ->toTree();

            $banners =  Banner::query()
                ->where('store_id', $request->get('store')['id'])
                ->where('banners.is_enabled', true)
                ->when($request->has('type'), function ($query) use ($request) {
                    $query->where('banners.type', $request->type);
                })
                ->with('sizeImages.pivot.interfacePosition')
                ->orderBy("sequence")
                ->get();

            $settings = Setting::where('store_id', $request->get('store')['id'])
                ->with('city.state', 'socialMedias')
                ->first();

            if ($settings) {
                $settings->append('logo_url');
            }

            unset($settings->payment_info);
            unset($settings->freight_info);

            $brands = Brand::query()
                ->select('id', 'name', 'image')
                ->where('is_enabled', true)
                ->where('is_public', true)
                ->orderBy('name')
                ->where('tenant_id',  $request->get('store')['tenant_id'])
                ->get()
                ->append('image_url');



            $homeSections = Section::query()
                ->where('is_enabled', true)
                ->where('store_id', $storeId)
                ->where('is_home', true)
                ->where(function ($query) use ($request) {
                    $query->whereHas('products', function ($query) use ($request) {
                        $query->where('products.is_enabled', true)
                            ->where('store_id', $request->get('store')['id']);
                    })->orWhereHas('auxiliarProducts', function ($query) use ($request) {
                        $query->where('products.is_enabled', true)
                            ->where('store_id', $request->get('store')['id']);
                    });
                })
                ->orderBy('order')
                ->with(['products' =>  function ($query) use ($request) {
                    $query->select(
                        'products.id',
                        DB::raw('COALESCE(products.description_reference, products.commercial_name) as commercial_name'),
                        'products.description',
                        'products.price',
                        'products.promotion_price',
                        'products.discount',
                        'products.spots',
                        'products.scores',
                        'products.quantity',
                        'products.is_grid',
                        'products.payment_condition',
                        'products.type_sale',
                        'products.is_enabled',
                        'measurement_units.initials as um',
                        'products.section_id',
                        'products.tag'
                    )
                        ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
                        ->where('products.is_enabled', true)
                        ->whereIn(DB::raw('(products.reference, (products.id + products.quantity))'), function ($query) {
                            $query->select(
                                'products.reference',
                                DB::raw('max(products.id + products.quantity)')
                            )
                                ->from(with(new Product)->getTable())
                                ->where('products.is_enabled', true)
                                ->groupBy('reference');
                        })
                        ->where('store_id', $request->get('store')['id'])
                        ->with('images', 'paymentMethods');
                }, 'auxiliarProducts' =>  function ($query) use ($request) {
                    $query->select(
                        'products.id',
                        DB::raw('COALESCE(products.description_reference, products.commercial_name) as commercial_name'),
                        'products.description',
                        'products.price',
                        'products.promotion_price',
                        'products.discount',
                        'products.spots',
                        'products.scores',
                        'products.quantity',
                        'products.is_grid',
                        'products.payment_condition',
                        'products.type_sale',
                        'products.is_enabled',
                        'measurement_units.initials as um',
                        'products.section_id'
                    )
                        ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
                        ->where('products.is_enabled', true)
                        ->whereIn(DB::raw('(products.reference, (products.id + products.quantity))'), function ($query) {
                            $query->select(
                                'products.reference',
                                DB::raw('max(products.id + products.quantity)')
                            )
                                ->from(with(new Product)->getTable())
                                ->where('products.is_enabled', true)
                                ->groupBy('reference');
                        })
                        ->where('store_id', $request->get('store')['id'])
                        ->where('products.is_enabled', true)
                        ->with('images', 'paymentMethods');
                }])
                ->orderBy('order')
                ->get([
                    'id', 'name', 'parent_id', '_lft', '_rgt',
                    'order', 'order_home', 'is_home', 'descriptive', 'image'
                ])->toArray();

            $sectionsHome = [];
            foreach ($homeSections as $section) {
                $section['products'] =  $section['products'] = array_merge($section['products'], $section['auxiliar_products']);
                unset($section['auxiliar_products']);
                $sectionsHome[] = $section;
            }

            foreach ($sectionsHome as &$section) {
                foreach ($section['products'] as &$item) {
                    if (empty($item['images'])) {
                        $item['images'] = [['name' => 'images/noimage.png']];
                    }
                }
            }

            $data = [
                'sections' => $sections,
                'banners' => $banners,
                'settings' => $settings,
                'brands' => $brands,
                'payment_methods' => $this->paymentMethods($storeId),
                'rank' => $this->rankProducts($storeId),
                'sections_home' => $sectionsHome,
                'home_blocks' => $this->homeBlocks($request, $storeId),
            ];

            Cache::put($cacheToken, $data, now()->addMinutes(2));
        }

        return $this->sendResponse($data);
    }

    private function homeBlocks(Request $request, int $storeId)
    {
        $tenantId = (int) ($request->get('store')['tenant_id'] ?? 0);

        return HomeBlock::query()
            ->with('items')
            ->where('store_id', $storeId)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (HomeBlock $block) use ($storeId, $tenantId) {
                $ids = $block->items->pluck('item_id')->all();
                $items = $this->resolveHomeBlockItems($block->type, $ids, $storeId, $tenantId);

                return [
                    'id' => $block->id,
                    'title' => $block->title,
                    'type' => $block->type,
                    'sort_order' => $block->sort_order,
                    'items' => $items,
                ];
            })
            ->values();
    }

    private function paymentMethods(int $storeId)
    {
        return PaymentMethod::query()
            ->where('is_enabled', true)
            ->whereHas('stores', fn ($query) => $query->where('stores.id', $storeId))
            ->orderBy('description')
            ->get(['id', 'description', 'code', 'icon']);
    }

    private function rankProducts(int $storeId)
    {
        $products = Product::query()
            ->select(
                'products.id',
                DB::raw('COALESCE(products.description_reference, products.commercial_name) as commercial_name'),
                'products.price',
                'products.promotion_price',
                'products.discount',
                'products.quantity',
                'products.is_enabled',
                'products.section_id',
                'products.product_view'
            )
            ->where('products.store_id', $storeId)
            ->where('products.is_enabled', true)
            ->with('images')
            ->orderByDesc('products.product_view')
            ->orderByDesc('products.id')
            ->limit(10)
            ->get();

        $products->each(function ($product) {
            if ($product->images->isEmpty()) {
                $product->images->push(['name' => 'images/noimage.png']);
            }
        });

        return $products;
    }

    private function resolveHomeBlockItems(string $type, array $ids, int $storeId, int $tenantId)
    {
        if (empty($ids)) {
            return collect();
        }

        $ordered = fn ($collection) => $collection
            ->sortBy(fn ($item) => array_search($item->id, $ids))
            ->values();

        if ($type === HomeBlock::TYPE_CATEGORIES) {
            return $ordered(Section::query()
                ->where('store_id', $storeId)
                ->where('is_enabled', true)
                ->whereIn('id', $ids)
                ->get(['id', 'name', 'descriptive', 'image', 'parent_id']));
        }

        if ($type === HomeBlock::TYPE_PRODUCTS) {
            $products = Product::query()
                ->select(
                    'products.id',
                    DB::raw('COALESCE(products.description_reference, products.commercial_name) as commercial_name'),
                    'products.description',
                    'products.price',
                    'products.promotion_price',
                    'products.discount',
                    'products.spots',
                    'products.scores',
                    'products.quantity',
                    'products.is_grid',
                    'products.payment_condition',
                    'products.type_sale',
                    'products.is_enabled',
                    'products.section_id',
                    'products.tag'
                )
                ->where('products.store_id', $storeId)
                ->where('products.is_enabled', true)
                ->whereIn('products.id', $ids)
                ->with('images', 'paymentMethods')
                ->get();

            $products->each(function ($product) {
                if ($product->images->isEmpty()) {
                    $product->images->push(['name' => 'images/noimage.png']);
                }
            });

            return $ordered($products);
        }

        if ($type === HomeBlock::TYPE_BRANDS) {
            return $ordered(Brand::query()
                ->where('tenant_id', $tenantId)
                ->where('is_enabled', true)
                ->where('is_public', true)
                ->whereIn('id', $ids)
                ->get(['id', 'name', 'image'])
                ->append('image_url'));
        }

        if ($type === HomeBlock::TYPE_BANNERS) {
            return $ordered(Banner::query()
                ->where('store_id', $storeId)
                ->where('is_enabled', true)
                ->whereIn('id', $ids)
                ->with('sizeImages.pivot.interfacePosition')
                ->get());
        }

        return collect();
    }
}
