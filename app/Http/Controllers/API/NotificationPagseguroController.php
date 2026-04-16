<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class NotificationPagseguroController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $order = Order::where('code_payment', $request->reference_id)->first();

        if ($order) {
            if ($request->charges[0]['status'] == "PAID") {
                $order->status = 2;
            } elseif ($request->charges[0]['status'] == "CANCELED") {
                $order->status = 8;
            }
            $order->save();
        }
    }
}
