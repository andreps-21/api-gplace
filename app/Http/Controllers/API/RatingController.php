<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->get('store')['id'];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $customer = Customer::query()->where('person_id', $request->user()->person_id)->first();
        if (! $customer) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.', [], 403);
        }

        $item = OrderItem::query()
            ->whereKey($request->integer('order_item_id'))
            ->whereHas('order', function ($q) use ($request, $customer) {
                $q->where('store_id', $this->storeId($request))
                    ->where('customer_id', $customer->id);
            })
            ->firstOrFail();

        $review = ProductReview::query()->updateOrCreate(
            [
                'product_id' => $item->product_id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $request->integer('rating'),
                'comment' => $request->input('comment', ''),
            ]
        );

        Product::query()
            ->whereKey($item->product_id)
            ->update([
                'rating' => ProductReview::query()->where('product_id', $item->product_id)->avg('rating') ?: 0,
            ]);

        return $this->sendResponse($review, 'Avaliação registrada.', 201);
    }
}
