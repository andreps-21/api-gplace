<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionHomeController extends BaseController
{
    public function index(Request $request)
    {
        $store = $request->get('store')['id'];


        $sections = Section::query()
            ->where('is_enabled', true)
            ->where('store_id', $store)
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
                    'products.tag',
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
                    'products.tag',
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

        $data = [];
        foreach ($sections as $section) {
            $section['products'] =  $section['products'] = array_merge($section['products'], $section['auxiliar_products']);
            unset($section['auxiliar_products']);
            $data[] = $section;
        }

        foreach ($data as &$section) {
            foreach ($section['products'] as &$item) {
                if (empty($item['images'])) {
                    $item['images'] = [['name' => 'images/noimage.png']];
                }
            }
        }

        return $this->sendResponse($data);
    }
}
