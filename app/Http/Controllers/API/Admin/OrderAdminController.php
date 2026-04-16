<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Listagem e detalhe de pedidos da loja (equivalente ao Blade `orders.index` / `show`, escopo admin).
 */
class OrderAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $customers = Customer::person()
            ->where('customers.status', 1)
            ->whereHas('tenants', function ($que) use ($request) {
                $que->where('tenants.id', (int) $request->attributes->get('store')['tenant_id']);
            })
            ->orderBy('name')
            ->get();

        $query = Order::query()
            ->with('customer.people')
            ->where('store_id', $storeId)
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->query('status'));
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('purchase_date', '>=', $request->query('start_date'));
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('purchase_date', '<=', $request->query('end_date'));
            })
            ->when($request->filled('customer'), function ($q) use ($request) {
                $q->where('customer_id', (int) $request->query('customer'));
            })
            ->when($request->has('sync'), function ($q) use ($request) {
                if (filter_var($request->query('sync'), FILTER_VALIDATE_BOOLEAN)) {
                    $q->whereNotNull('sync_at');
                } else {
                    $q->whereNull('sync_at');
                }
            })
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc');

        return $this->sendResponse([
            'orders' => $query->paginate($perPage),
            'customers' => $customers,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $item = Order::with([
            'customer.people',
            'salesman.people',
            'items.product.images',
            'payment',
            'address.city' => function ($query) {
                $query->stateName();
            },
        ])
            ->where('store_id', $storeId)
            ->whereKey($id)
            ->firstOrFail();

        return $this->sendResponse($item);
    }
}
