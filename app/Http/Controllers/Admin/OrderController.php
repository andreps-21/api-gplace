<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Salesman;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendContractorMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:orders_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:orders_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:orders_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:orders_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $customers = Customer::person()
            ->where('customers.status', 1)
            ->whereHas('tenants', function ($que) {
                return $que->where('tenant_id', session('store')['tenant_id']);
            })
            ->orderBy('name')
            ->get();

        $average = false;

        $data =  Order::query()
            ->with('customer.people')
            ->when(!empty($request->status), function ($query) use ($request){
                return $query->where('status', $request->status);
            })
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->whereDate('purchase_date', '>=', $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->whereDate('purchase_date', '<=', $request->end_date);
            })
            ->when(!empty($request->customer), function ($query) use ($request) {
                return $query->where('customer_id', $request->customer);
            })
            ->when(!empty($request->sync), function ($query) use ($request) {
                if (filter_var($request->sync, FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereNotNull('sync_at');
                } else {
                    $query->whereNull('sync_at');
                }
            })
            ->where('store_id', session('store')['id'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);


        return view('orders.index', compact('data', 'customers', 'average'));
    }

    public function create()
    {
        $products = Product::query()
            ->select(
                'products.id',
                'products.commercial_name',
                'measurement_units.initials',
                'sku'
            )
            ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
            ->where('products.is_enabled',  true)
            ->orderBy('products.commercial_name')
            ->where('store_id', session('store')['id'])
            ->get();

        $clients = Customer::person()
            ->where('customers.status', 1)
            ->orderBy('name')
            ->get();

        $payments = PaymentMethod::where('is_enabled', true)
            ->orderBy('description')
            ->get();

        return view('orders.create', compact(
            'products',
            'clients',
            'payments'
        ));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        DB::transaction(function ()  use ($request) {
                $inputs = $request->all();
                $inputs['vl_discount'] = moneyToFloat($request->vl_discount);
                $inputs['vl_amount'] = moneyToFloat($request->vl_amount);
                $inputs['vl_icms'] = moneyToFloat($request->vl_icms);
                $inputs['vl_ipi'] = moneyToFloat($request->vl_ipi);
                $inputs['vl_freight'] = moneyToFloat($request->vl_freight);
                $inputs['vl_spots'] = moneyToFloat($request->vl_spots);
                $inputs['total'] = moneyToFloat($request->vl_total);
                $order = Order::create($inputs);

                $total = count($request->product);
                for ($i = 0; $i < $total; $i++) {
                    $order->items()->create([
                        'code' => $i + 1,
                        'product_id' => $request->product[$i],
                        'um' => $request->um[$i],
                        'quantity' => moneyToFloat($request->quantity[$i]),
                        'value_unit' => moneyToFloat($request->value_unit[$i]),
                        'discount' => moneyToFloat($request->discount[$i]),
                        'total' => moneyToFloat($request->total[$i]),
                        'icms' => moneyToFloat($request->icms[$i]),
                        'ipi' => moneyToFloat($request->ipi[$i]),
                        'spots' => moneyToFloat($request->spots[$i]),
                    ]);
                };

                Mail::to($order->customer->people->email)->send(new SendContractorMail($order));
                
        });

        return redirect()->route('orders.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Order::with([
            'customer.people',
            'salesman.people',
            'items.product',
            'payment',
            'coupon',
            'address.city' => function($query){
                $query->stateName();
            }
        ])->findOrFail($id);

        return view('orders.show', compact('item'));
    }


    public function destroy($id)
    {
        $item = Order::with('items')->findOrFail($id);

        try {
            DB::beginTransaction();
            $item->items()->delete();
            $item->delete();
            DB::commit();
            return redirect()->route('orders.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('orders.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'code' => ['required', 'max:6'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required'],
            'customer_id' => ['required'],
            'salesman_id' => ['required'],
            'product' => ['required', 'array'],
            'um' => ['required', 'array'],
            'quantity' => ['required', 'array'],
            'value_unit' => ['required', 'array'],
            'total' => ['required', 'array'],
            'spots' => ['required', 'array'],
            'vl_amount' => ['required'],
            'vl_icms' => ['required'],
            'vl_ipi' => ['required'],
            'vl_discount' => ['required'],
            'vl_freight' => ['required'],
            'vl_total' => ['required'],
            'payment_method_id' => ['required'],
            'payment_condition' => ['required', 'max:30'],
            'type' => ['required'],
            'delivery_place' => ['nullable', 'max:120'],
            'description' => ['nullable', 'max:60'],
            'tracking_code' => ['nullable', 'max:20'],
            'vl_spots' => ['required', 'max:20'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
