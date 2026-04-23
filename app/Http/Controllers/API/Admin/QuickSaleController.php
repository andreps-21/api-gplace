<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Salesman;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuickSaleController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    /**
     * Próximo número de venda (numérico, 12 posições) com base nas vendas existentes da loja.
     */
    public function nextCode(Request $request)
    {
        $storeId = $this->storeId($request);
        $next = $this->nextNumericCode($storeId);

        return $this->sendResponse(['code' => str_pad((string) $next, 12, '0', STR_PAD_LEFT)]);
    }

    /**
     * Venda de balcão: regista pedido, itens, movimenta stock.
     */
    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $tenantId = $this->tenantId($request);

        $validator = Validator::make($request->all(), [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'salesman_id' => ['nullable', 'integer', 'exists:salesmen,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.value_unit' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['required', 'numeric', 'min:0'],
            'items.*.um' => ['required', 'string', 'max:20'],
            'vl_discount' => ['required', 'numeric', 'min:0'],
            'vl_surcharge' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        if (! Customer::query()
            ->where('id', (int) $request->input('customer_id'))
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->exists()) {
            return $this->sendError('Cliente inválido para esta loja.', [], 422);
        }

        if ($request->filled('salesman_id')) {
            if (! Salesman::query()
                ->where('id', (int) $request->input('salesman_id'))
                ->whereHas('stores', fn ($q) => $q->where('store_id', $storeId))
                ->exists()) {
                return $this->sendError('Vendedor inválido para esta loja.', [], 422);
            }
        }

        if (! PaymentMethod::query()
            ->where('id', (int) $request->input('payment_method_id'))
            ->where('is_enabled', true)
            ->whereHas('stores', fn ($q) => $q->where('stores.id', $storeId))
            ->exists()) {
            return $this->sendError('Forma de pagamento inválida para esta loja.', [], 422);
        }

        $items = $request->input('items', []);
        $bruto = 0.0;
        foreach ($items as $it) {
            $bruto += (float) $it['total'];
        }
        $vlDiscount = (float) $request->input('vl_discount');
        $vlSurcharge = (float) $request->input('vl_surcharge');
        $total = round($bruto - $vlDiscount + $vlSurcharge, 2);
        if ($total < 0) {
            return $this->sendError('Total da venda inválido.', [], 422);
        }

        $stockMovementService = app(StockMovementService::class);

        try {
            DB::beginTransaction();

            $qtyByProduct = [];
            foreach ($items as $item) {
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
                        'Estoque insuficiente para «'.$product->commercial_name.'».',
                        ['product_id' => $productId, 'available' => (int) $product->quantity, 'requested' => $needed],
                        422
                    );
                }
            }

            $code = str_pad((string) $this->nextNumericCode($storeId), 12, '0', STR_PAD_LEFT);

            $order = Order::create([
                'code' => $code,
                'code_payment' => uniqid('pdv_'),
                'status' => 2,
                'customer_id' => (int) $request->input('customer_id'),
                'salesman_id' => $request->input('salesman_id') ? (int) $request->input('salesman_id') : null,
                'vl_amount' => round($bruto, 2),
                'vl_icms' => 0,
                'vl_ipi' => 0,
                'vl_freight' => 0,
                'vl_discount' => round($vlDiscount, 2),
                'total' => $total,
                'payment_method_id' => (int) $request->input('payment_method_id'),
                'delivery_place' => 'Balcão',
                'description' => 'Venda rápida',
                'payment_condition' => 'À vista',
                'purchase_date' => now(),
                'type' => 2,
                'store_id' => $storeId,
                'address_id' => null,
                'vl_spots' => 0,
            ]);

            foreach ($items as $index => $item) {
                $line = [
                    'code' => $index + 1,
                    'product_id' => (int) $item['product_id'],
                    'um' => $item['um'],
                    'value_unit' => (float) $item['value_unit'],
                    'quantity' => (float) $item['quantity'],
                    'discount' => 0,
                    'spots' => 0,
                    'total' => (float) $item['total'],
                    'icms' => 0,
                    'ipi' => 0,
                ];

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
                        $request->user()?->id,
                        $order->id,
                        null,
                        null
                    );
                }

                $order->items()->create($line);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), [], 500);
        }

        $order->load(['customer' => function ($q) {
            $q->person();
        }, 'items.product', 'payment']);

        return $this->sendResponse($order, 'Venda registrada com sucesso.', 201);
    }

    private function nextNumericCode(int $storeId): int
    {
        $max = Order::query()
            ->where('store_id', $storeId)
            ->pluck('code')
            ->map(function ($c) {
                if (! is_string($c) && ! is_numeric($c)) {
                    return 0;
                }
                $s = (string) $c;

                return ctype_digit($s) ? (int) $s : 0;
            })
            ->max() ?? 0;

        $next = (int) $max + 1;
        if ($next > 999_999_999_999) {
            $next = (int) substr(preg_replace('/\D+/', '', (string) microtime(true)), 0, 12);
        }
        if ($next < 1) {
            $next = 1;
        }

        return $next;
    }
}
