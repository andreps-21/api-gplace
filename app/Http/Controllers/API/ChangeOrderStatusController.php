<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SendCancelOrder;

use App\Mail\SendConfirmPaymentOrder;
use App\Mail\SendRemovalOrder;
use App\Mail\SendContractorMail;
use App\Mail\SendTrackingCodeOrder;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ChangeOrderStatusController extends BaseController
{
    public function __invoke(Request $request)
    {
        $order = Order::with('customer.people', 'address', 'items.product', 'store.people.city.state')->findOrFail($request->id);

        $order->fill($request->all())->save();

        $settings = Setting::where('store_id', $order->store_id)->first();

        if ($order->status == 2) {
            Mail::to($order->customer->people->email)->send(new SendConfirmPaymentOrder($order, $settings));
        }

        if ($order->status == 5) {
            Mail::to($order->customer->people->email)->send(new SendRemovalOrder($order, $settings));
        }

        if ($order->status == 6) {
            Mail::to($order->customer->people->email)->send(new SendTrackingCodeOrder($order, $settings));
        }

        if ($order->status == 8) {
            Mail::to($order->customer->people->email)->send(new SendCancelOrder($order, $settings));
        }

        return $this->sendResponse([], "Mudança de status realizada com sucesso.");
    }
}
