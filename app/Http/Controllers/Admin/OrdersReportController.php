<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;

class OrdersReportController extends Controller
{
    public function __invoke(Request $request)
    {

        $customers = Customer::person()
            ->whereHas('tenants', function ($que) {
                return $que->where('tenant_id', session('store')['tenant_id']);
            })
            ->orderBy('name')
            ->get();

        $arrayCustomers = [];

        if ($request->has(['start_customer_id', 'end_customer_id'])) {
            for ($i = $request->start_customer_id; $i <= $request->end_customer_id; $i++) {
                array_push($arrayCustomers, $customers[$i]->id);
            }
        }

        if ($request->has(['start_date', 'end_date'])) {

            $data = DB::table('orders')
                ->select('orders.*', 'people.name', 'payment_methods.description as payment', 'coupons.name as coupom')
                ->join('payment_methods', function ($methods) {
                    $methods->on('payment_methods.id', '=', 'orders.payment_method_id');
                })
                ->leftjoin('coupons', function ($coupons) {
                    $coupons->on('coupons.id', '=', 'orders.coupon_id');
                })
                ->join('customers', function ($custom) use ($arrayCustomers) {
                    $custom->on('orders.customer_id', '=', 'customers.id')
                        ->join('people', 'people.id', '=', 'customers.person_id');
                })
                ->where(function ($query) use ($request) {
                    $query->whereDate('orders.created_at', '>=', $request->start_date)
                    ->whereDate('orders.created_at', '<=', $request->end_date);
                })
                ->when($request->filled('status'), function($query) use($request){
                    $query->where('orders.status', $request->status);
                })
                ->whereIn('orders.customer_id', $arrayCustomers)
                ->where('orders.store_id', session('store')['id'])
                ->get();
        } else {
            $data = null;
        }


        return view('orders.report', compact('data', 'customers'));
    }
}
