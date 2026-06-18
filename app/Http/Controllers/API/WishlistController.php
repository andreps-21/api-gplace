<?php

namespace App\Http\Controllers\API;

use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WishlistController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->get('store')['id'];
    }

    public function index(Request $request)
    {
        return $this->sendResponse(
            WishlistItem::query()
                ->with(['product.images'])
                ->where('store_id', $this->storeId($request))
                ->where('user_id', $request->user()->id)
                ->whereHas('product', fn ($q) => $q->where('store_id', $this->storeId($request)))
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function references(Request $request)
    {
        return $this->sendResponse(
            WishlistItem::query()
                ->where('store_id', $this->storeId($request))
                ->where('user_id', $request->user()->id)
                ->get(['id', 'product_id'])
        );
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
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item = WishlistItem::query()->firstOrCreate([
            'store_id' => $storeId,
            'user_id' => $request->user()->id,
            'product_id' => $request->integer('product_id'),
        ]);

        return $this->sendResponse($item->load('product.images'), 'Produto adicionado aos favoritos.', 201);
    }

    public function destroy(Request $request, int $id)
    {
        WishlistItem::query()
            ->where('store_id', $this->storeId($request))
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->delete();

        return $this->sendResponse(null);
    }
}
