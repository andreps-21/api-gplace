<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use App\Models\StockLot;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StockLotAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 20)));

        $validator = Validator::make($request->query(), [
            'product_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $productId = (int) $request->query('product_id');

        Product::query()->where('store_id', $storeId)->where('id', $productId)->firstOrFail();

        $paginator = StockLot::query()
            ->with(['warehouse:id,name,code'])
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->orderByDesc('received_at')
            ->paginate($perPage);

        return $this->sendResponse($paginator);
    }

    public function store(Request $request, StockMovementService $stockMovementService)
    {
        $storeId = $this->storeId($request);

        $validator = Validator::make($request->all(), [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'quantity_received' => ['required', 'integer', 'min:1'],
            'document_reference' => ['nullable', 'string', 'max:80'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'received_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();
        $qty = (int) $validated['quantity_received'];
        $productId = (int) $validated['product_id'];
        $receivedAt = isset($validated['received_at'])
            ? \Carbon\Carbon::parse($validated['received_at'])
            : now();

        $lot = null;
        DB::transaction(function () use ($storeId, $productId, $validated, $qty, $receivedAt, $stockMovementService, $request, &$lot) {
            $product = Product::query()->where('store_id', $storeId)->where('id', $productId)->lockForUpdate()->firstOrFail();

            $product->increment('quantity', $qty);
            $product->refresh();

            $lot = StockLot::query()->create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'quantity_received' => $qty,
                'quantity_remaining' => $qty,
                'document_reference' => $validated['document_reference'] ?? null,
                'unit_cost' => $validated['unit_cost'] ?? null,
                'received_at' => $receivedAt,
            ]);

            $note = $validated['note'] ?? null;
            if (! $note && ! empty($validated['document_reference'])) {
                $note = 'Ref. documento: '.$validated['document_reference'];
            }

            $stockMovementService->record(
                $storeId,
                $productId,
                $qty,
                (int) $product->quantity,
                StockMovement::TYPE_LOT_RECEIPT,
                $request->user()?->id,
                null,
                $lot->id,
                $note
            );
        });

        return $this->sendResponse($lot?->load('warehouse'), '', 201);
    }
}
