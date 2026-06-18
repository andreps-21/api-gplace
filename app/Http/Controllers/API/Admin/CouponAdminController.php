<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\BusinessUnit;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Coupon::query()
            ->with('businessUnit:id,name,description')
            ->where('store_id', $storeId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('description', 'LIKE', "%{$s}%");
                });
            })
            ->when($request->filled('start_at'), fn ($q) => $q->whereDate('end_at', '>=', $request->query('start_at')))
            ->when($request->filled('end_at'), fn ($q) => $q->whereDate('end_at', '<=', $request->query('end_at')))
            ->when($request->has('is_enabled'), fn ($q) => $q->where('is_enabled', filter_var($request->query('is_enabled'), FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('start_at');

        return $this->sendResponse([
            'coupons' => $query->paginate($perPage),
            'business_units' => BusinessUnit::query()
                ->where('tenant_id', $this->tenantId($request))
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name', 'description']),
            'sponsors' => Coupon::sponsors(),
            'applies' => Coupon::applies(),
        ]);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $validator = Validator::make($request->all(), $this->rules($request, $storeId));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $coupon = DB::transaction(function () use ($request, $storeId) {
            $data = $request->all();
            $data['store_id'] = $storeId;
            $data['discount'] = (float) $request->input('discount');
            $data['min_order'] = (float) $request->input('min_order');
            $data['balance'] = (int) $request->input('quantity');

            return Coupon::query()->create($data);
        });

        return $this->sendResponse($coupon->load('businessUnit:id,name,description'), 'Cupom criado.', 201);
    }

    public function show(Request $request, int $id)
    {
        return $this->sendResponse(
            Coupon::query()
                ->with('businessUnit:id,name,description')
                ->where('store_id', $this->storeId($request))
                ->whereKey($id)
                ->firstOrFail()
        );
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $coupon = Coupon::query()->where('store_id', $storeId)->whereKey($id)->firstOrFail();

        $validator = Validator::make($request->all(), $this->rules($request, $storeId, $coupon->id));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $request->all();
        $data['discount'] = (float) $request->input('discount');
        $data['min_order'] = (float) $request->input('min_order');
        $data['balance'] = max(0, (int) $coupon->balance + ((int) $request->input('quantity') - (int) $coupon->quantity));

        $coupon->fill($data)->save();

        return $this->sendResponse($coupon->fresh()->load('businessUnit:id,name,description'));
    }

    public function destroy(Request $request, int $id)
    {
        $coupon = Coupon::query()->where('store_id', $this->storeId($request))->whereKey($id)->firstOrFail();

        try {
            $coupon->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(Request $request, int $storeId, ?int $primaryKey = null): array
    {
        return [
            'name' => ['required', 'max:15', Rule::unique('coupons', 'name')->where('store_id', $storeId)->ignore($primaryKey)],
            'description' => ['nullable', 'max:40'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date'],
            'sponsor' => ['nullable', Rule::in(array_keys(Coupon::sponsors()))],
            'apply' => ['required', Rule::in(array_keys(Coupon::applies()))],
            'business_unit_id' => ['nullable', 'integer', Rule::exists('business_units', 'id')->where(fn ($q) => $q->where('tenant_id', $this->tenantId($request)))],
            'is_enabled' => ['required', 'boolean'],
            'discount' => ['required', 'numeric', 'min:0'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
