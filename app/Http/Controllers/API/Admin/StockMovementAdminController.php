<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockMovementAdminController extends BaseController
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

        $paginator = StockMovement::query()
            ->with(['user:id,name', 'order:id,code'])
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->sendResponse($paginator);
    }
}
