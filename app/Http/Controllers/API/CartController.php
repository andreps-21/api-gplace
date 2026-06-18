<?php

namespace App\Http\Controllers\API;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->get('store')['id'];
    }

    public function index(Request $request)
    {
        return $this->sendResponse([
            'cart' => $this->cartItems($request),
        ]);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);

        $validator = Validator::make($request->all(), [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('store_id', $storeId)->where('is_enabled', true)),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
            'isButton' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $product = Product::query()
            ->where('store_id', $storeId)
            ->whereKey($request->integer('product_id'))
            ->firstOrFail();

        $quantity = $request->integer('quantity');
        if ($quantity > (int) $product->quantity) {
            return $this->sendError(
                'Estoque insuficiente para «'.$product->commercial_name.'».',
                ['available' => (int) $product->quantity, 'requested' => $quantity],
                422
            );
        }

        $current = CartItem::query()
            ->where('store_id', $storeId)
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        $finalQuantity = $request->boolean('isButton')
            ? (int) ($current?->quantity ?? 0) + $quantity
            : $quantity;

        if ($finalQuantity > (int) $product->quantity) {
            return $this->sendError(
                'Estoque insuficiente para «'.$product->commercial_name.'».',
                ['available' => (int) $product->quantity, 'requested' => $finalQuantity],
                422
            );
        }

        if ($finalQuantity === 0) {
            CartItem::query()
                ->where('store_id', $storeId)
                ->where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->delete();
        } else {
            CartItem::query()->updateOrCreate(
                [
                    'store_id' => $storeId,
                    'user_id' => $request->user()->id,
                    'product_id' => $product->id,
                ],
                ['quantity' => $finalQuantity]
            );
        }

        return $this->sendResponse([
            'cart' => $this->cartItems($request),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        CartItem::query()
            ->where('store_id', $this->storeId($request))
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->delete();

        return $this->sendResponse([
            'cart' => $this->cartItems($request),
        ]);
    }

    private function cartItems(Request $request)
    {
        return CartItem::query()
            ->with(['product.images'])
            ->where('store_id', $this->storeId($request))
            ->where('user_id', $request->user()->id)
            ->whereHas('product', fn ($q) => $q->where('store_id', $this->storeId($request)))
            ->orderByDesc('updated_at')
            ->get();
    }
}
