<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Lista paginada de pedidos da loja (formato esperado pelo dashboard Next.js).
 */
class SalesListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $storeId = (int) $request->get('store')['id'];
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(10000, max(1, (int) $request->query('per_page', 15)));

        $sellerId = $request->query('seller_id');
        $sellerId = $sellerId !== null && $sellerId !== '' ? (int) $sellerId : null;

        $establishmentId = $request->query('establishment_id');
        if ($establishmentId !== null && $establishmentId !== '' && (int) $establishmentId !== $storeId) {
            return $this->sendError('Estabelecimento não corresponde à loja autenticada.', [], 403);
        }

        $query = Order::query()
            ->with([
                'salesman.people',
                'items.product',
                'store.people',
            ])
            ->where('store_id', $storeId)
            ->where('status', '!=', 8)
            ->when($sellerId, fn ($q) => $q->where('salesman_id', $sellerId))
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('purchase_date', '>=', $request->query('date_from'));
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('purchase_date', '<=', $request->query('date_to'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->query('status'));
            })
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function (Order $order) {
            $firstItem = $order->items->first();
            $product = $firstItem?->product;
            $productName = $product
                ? (string) ($product->description_reference ?: $product->commercial_name)
                : '—';

            $storeName = $order->relationLoaded('store') && $order->store?->people
                ? $order->store->people->name
                : null;

            return [
                'id' => $order->id,
                'total_price' => (float) ($order->total ?? $order->vl_amount),
                'created_at' => $order->created_at?->toIso8601String(),
                'purchase_date' => $order->purchase_date,
                'product_name' => $productName,
                'seller' => [
                    'name' => $order->salesman?->people?->name ?? '—',
                ],
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $productName,
                ] : null,
                'establishment' => [
                    'name' => $storeName ?? 'Loja',
                ],
            ];
        });

        $payload = $paginator->toArray();
        $payload['data'] = $paginator->items();

        return $this->sendResponse($payload);
    }
}
