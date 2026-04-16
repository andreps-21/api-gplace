<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\Coupon;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:coupons_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:coupons_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:coupons_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:coupons_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Coupon::when(!empty($request->start_at), function ($query) use ($request) {
                return $query->whereDate('end_at', '>=',$request->start_at);
            })
            ->when(!empty($request->end_at), function ($query) use ($request) {
                return $query->whereDate('end_at', '<=', $request->end_at);
            })
            ->when(isset($request->is_enabled), function ($query) use ($request) {
                return $query->where('is_enabled', $request->is_enabled);
            })
            ->where('store_id', session('store')['id'])
            ->orderBy('start_at', 'desc')
            ->paginate(10);

        return view('coupons.index', compact('data'));
    }

    public function create()
    {
        $businessUnits = BusinessUnit::orderBy('name')
            ->where('is_enabled', true)
            ->where('tenant_id', session('tenant')['id'])
            ->get();

        return view('coupons.create', compact('businessUnits'));
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules($request));


        DB::transaction(function () use ($request) {
            $inputs = $request->all();
            $inputs['discount'] = moneyToFloat($request->discount);
            $inputs['min_order'] = moneyToFloat($request->min_order);
            $inputs['balance'] = $request->quantity;
            $inputs['store_id'] = session('store')['id'];

            Coupon::create($inputs);
        });
        return redirect()->route('coupons.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Coupon::findOrFail($id);

        return view('coupons.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Coupon::findOrFail($id);


        $businessUnits = BusinessUnit::orderBy('name')
            ->where('is_enabled', true)
            ->get();

        return view('coupons.edit', compact('item', 'businessUnits'));
    }

    public function update(Request $request, $id)
    {
        $item = Coupon::findOrFail($id);

        $this->validate($request, $this->rules($request, $item->getKey()));

        $inputs = $request->all();
        $inputs['discount'] = moneyToFloat($request->discount);
        $inputs['min_order'] = moneyToFloat($request->min_order);
        $inputs['balance'] = $item->balance + ($request->quantity - $item->quantity);

        $item->fill($inputs)->save();

        return redirect()->route('coupons.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Coupon::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('coupons.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('coupons.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:15', Rule::unique('coupons')->where('store_id', session('store')['id'])->ignore($primaryKey)],
            'description' => ['nullable', 'max:40'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date'],
            'sponsor' => ['nullable'],
            'apply' => ['required'],
            'business_unit_id' => ['nullable'],
            'is_enabled' => ['required'],
            'discount' => ['required'],
            'min_order' => ['required'],
            'quantity' => ['required', 'integer', 'min:1']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
