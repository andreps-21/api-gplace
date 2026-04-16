<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Support\Facades\DB;

class ProductsReportController extends Controller
{
    public function __invoke(Request $request)
    {

        $products =  Product::where('store_id', session('store')['id'])
            ->where('is_enabled', true)
            ->orderBy('commercial_name')
            ->get();

        $brands = Brand::query()
            ->select('id', 'name')
            ->where('is_enabled', true)
            ->orderBy('name')
            ->where('tenant_id', session('store')['tenant_id'])
            ->get();

        $sections = Section::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->where('type', 'A')
            ->orderBy('name')
            ->get();


        $arrayProducts = [];

        if ($request->has(['start_product', 'end_product'])) {
            for ($i = $request->start_product; $i <= $request->end_product; $i++) {
                array_push($arrayProducts, $products[$i]->id);
            }
        }

        if ($request->has(['start_date', 'end_date'])) {

            $data = DB::table('order_items')
                ->select(
                    'products.commercial_name',
                    DB::raw('sum(order_items.quantity) as quantity'),
                    'orders.status',
                    'people.name',
                    DB::raw('DATE(orders.created_at) AS created_at')
                )
                ->join('products', function ($product) {
                    $product->on('order_items.product_id', '=', 'products.id');
                })
                ->join('orders', function ($order) use ($request) {
                    $order->on('order_items.order_id', '=', 'orders.id')
                        ->join('customers', function ($custom) use ($request) {
                            $custom->on('orders.customer_id', '=', 'customers.id')
                                ->join('people', 'people.id', '=', 'customers.person_id');
                        })->whereDate('orders.created_at', '>=', $request->start_date)
                        ->whereDate('orders.created_at', '<=', $request->end_date);
                })->groupBy('commercial_name', 'name', 'created_at', 'status')
                ->whereIn('order_items.product_id', $arrayProducts)
                ->where('products.store_id', session('store')['id'])
                ->when($request->filled('banrd'), function ($query) use ($request) {
                    $query->where('products.brand_id', $request->brand);
                })
                ->when(!empty($request->section_id), function ($q) use ($request) {
                    return $q->where('products.section_id', $request->section_id);
                })
                ->get();
        } else {
            $data = null;
        }
        return view('products.report', compact('data', 'products', 'brands', 'sections'));
    }
}
