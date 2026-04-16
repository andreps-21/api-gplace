<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Coupon;
use App\Rules\CpfCnpj;
use App\Models\Product;
use App\Models\Customer;
use App\Mail\SendOrderMail;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Actions\ValidateCoupon;
use Illuminate\Validation\Rule;
use App\Actions\PixPaymentAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Actions\TicketPaymentAction;
use Illuminate\Support\Facades\Mail;
use App\Actions\CreditCardPaymentAction;
use App\Mail\SendContractorMail;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;

class OrderController extends BaseController
{
    public function index(Request $request)
    {

        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        };

        $data =  Order::with([
            'customer.people',
            'salesman.people',
            'items.product.images',
            'payment',
            'address.city' => function ($query) {
                $query->stateName();
            }
        ])
            ->where('customer_id', $client->id)
            ->where('store_id', $request->get('store')['id'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();


        return $this->sendResponse($data);
    }

    public function show(Request $request, $id)
    {
        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.', [], 403);
        }
        $item = Order::with([
            'customer.people',
            'salesman.people',
            'items.product.images',
            'payment',
            'address.city' => function ($query) {
                $query->stateName();
            }
        ])
            ->where('store_id', $request->get('store')['id'])
            ->where('customer_id', $client->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($client->id != $item->customer_id) {
            return $this->sendError('Você não pode acessar esse pedido.', [], 403);
        }

        return $this->sendResponse($item);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        Request $request,
        TicketPaymentAction $ticketAction,
        CreditCardPaymentAction $creditCardAction,
        ValidateCoupon $validateAction,
        PixPaymentAction $pixAction,
    ) {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $client = Customer::person()->where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }

        if ($request->coupon_id) {
            $coupon = Coupon::find($request->coupon_id);


            $subTotal = collect($request->items)->sum('total');
            try {
                $coupon = $validateAction->execute(
                    $coupon->name,
                    $subTotal
                );
            } catch (\Throwable $th) {
                return $this->sendError($th->getMessage(), [], 403);
            }
        }


        try {
            DB::beginTransaction();
            $storeId = $request->get('store')['id'];
            $inputs = $request->all();
            $stockMovementService = app(StockMovementService::class);

            $qtyByProduct = [];
            foreach ($request->items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (float) $item['quantity'];
                $qtyByProduct[$pid] = ($qtyByProduct[$pid] ?? 0) + $qty;
            }

            foreach ($qtyByProduct as $productId => $needed) {
                $product = Product::query()
                    ->where('store_id', $storeId)
                    ->where('id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    DB::rollBack();

                    return $this->sendError('Produto não encontrado nesta loja.', [], 422);
                }

                if ((float) $product->quantity < $needed) {
                    DB::rollBack();

                    return $this->sendError(
                        'Estoque insuficiente para «'.$product->commercial_name.'». Disponível: '.(int) $product->quantity.'.',
                        ['product_id' => $productId, 'available' => (int) $product->quantity, 'requested' => $needed],
                        422
                    );
                }
            }

            $inputs['code'] = str_pad(rand(0, '9' . round(microtime(true))), 11, "0", STR_PAD_LEFT);
            $inputs['code_payment'] = uniqid();
            $inputs['status'] = 1;
            $inputs['customer_id'] = $client->id;
            $inputs['vl_icms'] = 0;
            $inputs['vl_ipi'] = 0;
            $inputs['purchase_date'] = now();
            $inputs['store_id'] = $storeId;


            $order = Order::create($inputs);

            foreach ($request->items as $index => $item) {
                $item['code'] = $index + 1;
                $item['icms'] = 0;
                $item['ipi'] = 0;

                $pid = (int) $item['product_id'];
                $soldQty = (float) $item['quantity'];

                Product::where('store_id', $storeId)->where('id', $pid)->decrement('quantity', $soldQty);

                $lineProduct = Product::query()->where('store_id', $storeId)->where('id', $pid)->first();
                if ($lineProduct) {
                    $delta = - (int) round($soldQty);
                    $stockMovementService->record(
                        $storeId,
                        $pid,
                        $delta,
                        (int) $lineProduct->quantity,
                        StockMovement::TYPE_ORDER_SALE,
                        null,
                        $order->id,
                        null,
                        null
                    );
                }

                $order->items()->create($item);
            }

            $order->load(['customer' => function ($query) {
                $query->person();
            }, 'items.product', 'address.city', 'payment', 'store' => function ($query) {
                $query->person();
            }]);


            if ($order->payment->code == PaymentMethod::TICKET) {
                $checkout = $ticketAction->execute($storeId, $order, $request);
                $order->ticket_link = $checkout['ticket_url'];
                $order->return_payment = $checkout;
            } else if ($order->payment->code == PaymentMethod::CREDIT_CARD) {
                $checkout = $creditCardAction->execute($storeId, $order, $request);
                $order->return_payment = $checkout;

                if (!$checkout['payed']) {
                    DB::rollBack();
                    return $this->sendError($checkout['payment_response']['message'],  $checkout, 403);
                }

                $order->status = 2;
            } else if ($order->payment->code == PaymentMethod::PIX) {
                $checkout = $pixAction->execute($storeId, $order, $request);
                $order->return_payment = $checkout;
            }

            if (isset($coupon)) {
                $coupon->decrement('balance');
            }

            $order->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage(), [], 403);
        }

        $settings = Setting::where('store_id', $storeId)->first();

        try {
            if(isset($settings)){
                Mail::to(auth()->user()->email)->send(new SendOrderMail($order, $settings));
                if ($settings->email_notification) {
                    Mail::to($settings->email_notification)->send(new SendContractorMail($order));
                }
            }
        } catch (\Throwable $th) {
            Log::error("Erro no envio do email:" . $th->getMessage());
        }

        return $this->sendResponse($order, 'Pedido criado com sucesso.');
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $paymentMethod = PaymentMethod::find($request->payment_method_id);

        $rules = [
            'vl_amount' => ['required', 'numeric'],
            'vl_freight' => ['required', 'numeric'],
            'vl_discount' => ['required', 'numeric'],
            'vl_spots' => ['required', 'integer'],
            'password_donuz' => [Rule::requiredIf($request->vl_spots > 0), 'integer'],
            'coupon_id' => ['nullable', 'exists:coupons,id'],
            'total' => ['required', 'numeric'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'delivery_place' => ['nullable', 'string'],
            'address_id' => [Rule::requiredIf($request->type == 1), 'nullable', 'exists:addresses,id'],
            'payment_condition' => ['required'],
            'type' => ['required', 'integer'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.um' => ['required', 'string'],
            'items.*.value_unit' => ['required', 'numeric'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.discount' => ['required', 'numeric'],
            'items.*.spots' => ['required', 'numeric'],
            'items.*.total' => ['required', 'numeric'],
            'card.number' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.encrypted'))],
            'card.month' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.encrypted')), 'min:2', 'max:2'],
            'card.year' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.encrypted')), 'min:4', 'max:4'],
            'card.security_code' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.encrypted')), 'min:3', 'max:3'],
            'card.holder_name' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.encrypted')), 'string'],
            'card.encrypted' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD && !$request->has('card.number')), 'string'],
            'card.installments' => [Rule::requiredIf($paymentMethod->code == PaymentMethod::CREDIT_CARD), 'integer'],
            'customer.name' => ['required', 'string', 'max:34'],
            'customer.email' => ['required', 'email'],
            'customer.nif' => ['required', new CpfCnpj],
            'customer.ddd' => ['required', 'min:2', 'max:2'],
            'customer.phone' => ['required', 'max:9'],
            'address' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable'],
            'address.street' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'string', 'max:70'],
            'address.number' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'string', 'max:10'],
            'address.complement' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'string'],
            'address.district' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'string', 'max:50'],
            'address.city' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'exists:cities,id'],
            'address.zip_code' => [Rule::requiredIf($request->type == 1 || $paymentMethod->code == PaymentMethod::CREDIT_CARD || $paymentMethod->code == PaymentMethod::TICKET), 'nullable', 'string', 'max:8'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
