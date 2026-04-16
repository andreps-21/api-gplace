<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\API\ProductService;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Section;
use App\Models\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->select(
                'products.id',
                'products.description_reference',
                'products.commercial_name',
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
                'products.tag',
                'measurement_units.initials as um'
            )
            ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
            ->when($request->has('section'), function ($query) use ($request) {
                $sections = Section::descendantsAndSelf($request->section);
                return  $query->where(function ($que) use ($sections) {
                    $que->whereIn('products.section_id', $sections->pluck('id')->all())
                        ->orWhereHas('sections', function ($q) use ($sections) {
                            $q->whereIn('sections.id', $sections->pluck('id')->all());
                        });
                });
            })
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('products.type', $request->type);
            })
            ->when($request->has('price_start'), function ($query) use ($request) {
                $query->where('products.price', '>=', $request->price);
            })
            ->when($request->has('price_end'), function ($query) use ($request) {
                $query->where('products.price', '<=', $request->price);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                return  $query->where(function ($que) use ($request) {
                    return $que->where('products.commercial_name', 'like', '%' . $request->search . '%')
                        ->orWhere('products.description', 'like', '%' . $request->search . '%');
                });
            })
            ->where('products.is_enabled', true)
            ->whereIn(DB::raw('(products.reference, (products.id + products.quantity))'), function ($query) {
                $query->select(
                    'products.reference',
                    DB::raw(' max(products.id + products.quantity)')
                )
                    ->from(with(new Product)->getTable())
                    ->where('products.is_enabled', true)
                    ->groupBy('reference');
            })
            ->where('store_id', $request->get('store')['id'])
            ->with('images', 'paymentMethods')
            ->paginate(25);

        $products->getCollection()->transform(function ($item){

            if ($item->images->isEmpty()) {
                $item->images->push(['name' => 'images/noimage.png']);
            }

            return $item;
        });

        return $this->sendResponse($products);
    }


    public function indexTeste(Request $request)
    {
        $products = $this->productService->search($request);

        return $this->sendResponse($products);
    }

    public function show(Request $request, $id)
    {
        $product = Product::info()
            ->with([
                'images',
                'paymentMethods',
                'variation.grid.variation',
                'productsGrid' => function ($query) use($request) {
                    $query->where('products.store_id', $request->get('store')['id'])
                        ->with('images', 'variation', 'products.images');
                },
                'products.images',
                'products.paymentMethods'
            ])
            ->where('products.store_id', $request->get('store')['id'])
            ->where('products.id', $id)
            ->firstOrFail();

            $this->addDefaultImageIfEmpty($product->images);

            foreach ($product->productsGrid as $grid) {
                $this->addDefaultImageIfEmpty($grid->images);

                foreach ($grid->products as $prod) {
                    $this->addDefaultImageIfEmpty($prod->images);
                }
            }

            foreach ($product->products as $p) {
                $this->addDefaultImageIfEmpty($p->images);
            }

        $usedVariations = $product->productsGrid
            ->flatten()
            ->pluck('variation')
            ->collapse()
            ->pluck('id')
            ->unique()
            ->all();

        $product->grid = $product->variation
            ->flatten()
            ->pluck('grid')
            ->map(function ($grid) use ($usedVariations) {
                $variations = $grid->variation->filter(function ($variation) use ($usedVariations) {
                    return in_array($variation->id, $usedVariations);
                });
                return [
                    'grid' => $grid->grid,
                    'description' => $grid->description,
                    'is_enabled' => $grid->is_enabled,
                    'type' => $grid->type,
                    'variation' => $variations
                ];
            });

        unset($product->variation);

        $product->increment('product_view');
        return $this->sendResponse($product);
    }

    private function addDefaultImageIfEmpty($images)
    {
        if ($images->isEmpty()) {
            $images->push(new ProductImage(['name' => 'images/noimage.png']));
        }
    }

    public function getGrid(Request $request)
    {
        $array_products = [];
        $product = Product::where('reference', '=', $request->data)->get();
        if (!empty($product)) {
            foreach ($product as $item) {
                array_push($array_products, ['id' => $item->id, 'commercial_name' => $item->commercial_name, 'price' => "R$ " . number_format($item->price, 2, ',', '.'), 'promotion_price' => "R$ " . number_format($item->promotion_price, 2, ',', '.'), 'quantity' => $item->quantity]);
            }
        }
        return json_encode($array_products);
    }
}
