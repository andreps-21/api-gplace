<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderPrintController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $item = Order::with([
            'customer.people',
            'salesman.people',
            'items.product',
            'payment',
            'coupon',
            'address.city' => function($query){
                $query->stateName();
            },
            'store'
        ])->findOrFail($id);

        $data = $item->items;
        $quant=0;

        foreach( $data as $obj)
        {
            $quant += $obj->quantity;
        }

        return view('orders.order-print', compact('item','quant'));
    }
}
