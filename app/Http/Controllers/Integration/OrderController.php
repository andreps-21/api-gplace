<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Mail\SendCancelOrder;
use App\Mail\SendConfirmPaymentOrder;
use App\Mail\SendRemovalOrder;
use App\Mail\SendTrackingCodeOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends BaseController
{
    public function index(Request $request)
    {

        $data =  Order::query()
            ->where('store_id', $request->get('store')['id'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->paginate(25);


        return $this->sendResponse($data);
    }

    public function show(Request $request, $id)
    {
        $item = Order::with([
            'customer.people',
            'salesman.people',
            'items.product',
            'payment',
            'address.city' => function ($query) {
                $query->stateName();
            }
        ])
            ->where('store_id', $request->get('store')['id'])
            ->where('id', $id)
            ->firstOrFail();

        return $this->sendResponse($item);
    }

    public function update(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $order = Order::with('customer.people', 'address', 'items.product', 'store.people.city.state')
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();

        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $id)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $order->fill($request->all())->save();

        if ($order->status == 2) {
            Mail::to($order->customer->people->email)->send(new SendConfirmPaymentOrder($order));
        }

        if ($order->status == 5) {
            $address = $order->store->people->street . ", " . $order->store->people->number . ", " . $order->store->people->district . " - " . $order->store->people->city->title . " - " . $order->store->people->city->state->letter;

            Mail::to($order->customer->people->email)->send(new SendRemovalOrder($order, $address));
        }

        if ($order->status == 6) {
            Mail::to($order->customer->people->email)->send(new SendTrackingCodeOrder($order));
        }

        if ($order->status == 8) {
            Mail::to($order->customer->people->email)->send(new SendCancelOrder($order));
        }

        return $this->sendResponse([], "Mudança de status realizada com sucesso.");
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'status' => ['required', 'integer', 'min:1', 'max:8'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
