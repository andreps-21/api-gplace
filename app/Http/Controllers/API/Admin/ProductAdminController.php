<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Product::with('variation')
            ->info()
            ->where('products.store_id', $storeId)
            ->when($request->has('is_enabled'), function ($q) use ($request) {
                $q->where('products.is_enabled', $request->boolean('is_enabled'));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where('products.commercial_name', 'LIKE', "%{$s}%");
            })
            ->when($request->filled('section_id'), function ($q) use ($request) {
                $q->where('products.section_id', (int) $request->query('section_id'));
            })
            ->when($request->filled('brand_id'), function ($q) use ($request) {
                $q->where('products.brand_id', (int) $request->query('brand_id'));
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('products.type', $request->query('type'));
            })
            ->when($request->filled('sku'), function ($q) use ($request) {
                $s = $request->query('sku');
                $q->where('products.sku', 'LIKE', "%{$s}%");
            })
            ->orderBy('products.commercial_name');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $product = null;
        DB::transaction(function () use ($request, $storeId, $validator, &$product) {
            $validated = $validator->validated();
            $note = $validated['stock_change_note'] ?? null;
            $inputs = Arr::except($this->normalizeMoney($validated), ['payment_methods', 'sections', 'stock_change_note']);
            $inputs['store_id'] = $storeId;
            $inputs['origin'] = $inputs['origin'] ?? 1;
            $inputs['type'] = $inputs['type'] ?? Product::PRODUCT;
            $inputs['is_grid'] = $inputs['is_grid'] ?? '0';

            $product = Product::create($inputs);

            if (! empty($request->payment_methods)) {
                $product->paymentMethods()->sync($request->payment_methods);
            }
            if (! empty($request->sections)) {
                $product->sections()->sync($request->sections);
            }

            $qty = (int) $product->quantity;
            app(StockMovementService::class)->record(
                $storeId,
                $product->id,
                $qty,
                $qty,
                StockMovement::TYPE_ADMIN_CREATE,
                $request->user()?->id,
                null,
                null,
                $note
            );
        });

        return $this->sendResponse($product->load(['paymentMethods', 'sections']), '', 201);
    }

    public function show(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::with('variation')
            ->info()
            ->where('products.store_id', $storeId)
            ->where('products.id', $id)
            ->firstOrFail();

        return $this->sendResponse($product);
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::query()->where('store_id', $storeId)->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($request, $product->id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $product, $validator) {
            $validated = $validator->validated();
            $note = $validated['stock_change_note'] ?? null;
            $qtyBefore = (int) $product->quantity;

            $inputs = Arr::except($this->normalizeMoney($validated), ['payment_methods', 'sections', 'stock_change_note']);
            $product->fill($inputs)->save();

            if ($request->has('payment_methods')) {
                $product->paymentMethods()->sync($request->payment_methods ?? []);
            }
            if ($request->has('sections')) {
                $product->sections()->sync($request->sections ?? []);
            }

            $product->refresh();
            $qtyAfter = (int) $product->quantity;
            if ($qtyBefore !== $qtyAfter) {
                app(StockMovementService::class)->record(
                    (int) $product->store_id,
                    $product->id,
                    $qtyAfter - $qtyBefore,
                    $qtyAfter,
                    StockMovement::TYPE_ADMIN_ADJUST,
                    $request->user()?->id,
                    null,
                    null,
                    $note
                );
            }
        });

        return $this->sendResponse($product->fresh()->load(['paymentMethods', 'sections']));
    }

    public function destroy(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::query()->where('store_id', $storeId)->findOrFail($id);

        DB::beginTransaction();
        try {
            $product->images()->delete();
            $product->products()->detach();
            $product->sections()->detach();
            $product->paymentMethods()->detach();
            $product->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function normalizeMoney(array $data): array
    {
        foreach (['price', 'discount', 'weight', 'cubic_weight', 'length', 'width', 'height', 'promotion_price'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && ! is_float($data[$key])) {
                $data[$key] = function_exists('moneyToFloat') ? moneyToFloat($data[$key]) : (float) $data[$key];
            }
        }
        if (array_key_exists('quantity', $data) && $data['quantity'] !== null) {
            $data['quantity'] = (int) $data['quantity'];
        }
        if (array_key_exists('min_stock', $data) && $data['min_stock'] !== null) {
            $data['min_stock'] = (int) $data['min_stock'];
        }

        return $data;
    }

    private function rules(Request $request, ?int $productId = null): array
    {
        $isGrid = (bool) $request->input('is_grid');
        $storeId = $this->storeId($request);

        return [
            'video' => ['nullable', 'url', 'max:50'],
            'reference' => [
                'required',
                'max:15',
                Rule::unique('products', 'reference')->where(fn ($q) => $q->where('store_id', $storeId))->ignore($productId),
            ],
            'origin' => ['nullable', 'max:1', 'numeric'],
            'commercial_name' => ['required', 'max:60'],
            'description_reference' => [Rule::requiredIf($isGrid), 'nullable', 'max:60'],
            'description' => ['nullable'],
            'um_id' => ['required', 'exists:measurement_units,id'],
            'tag' => ['nullable'],
            'price' => ['required'],
            'promotion_price' => ['required'],
            'discount' => ['nullable'],
            'payment_condition' => ['nullable', 'max:30'],
            'weight' => ['nullable'],
            'width' => ['required'],
            'height' => ['required'],
            'length' => ['required'],
            'cubic_weight' => ['nullable'],
            'brand_id' => ['required', 'exists:brands,id'],
            'about' => ['nullable', 'max:200'],
            'recommendation' => ['nullable', 'max:200'],
            'benefits' => ['nullable', 'max:200'],
            'formula' => ['nullable', 'max:200'],
            'application_mode' => ['nullable', 'max:200'],
            'dosage' => ['nullable', 'max:200'],
            'lack' => ['nullable', 'max:60'],
            'other_information' => ['nullable', 'max:400'],
            'is_enabled' => ['required'],
            'type' => ['required', 'max:1'],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'family_id' => ['nullable', 'exists:families,id'],
            'presentation_id' => ['nullable', 'exists:presentations,id'],
            'sku' => ['nullable', 'max:32'],
            'is_grid' => ['nullable'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'stock_change_note' => ['nullable', 'string', 'max:500'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['integer', 'exists:payment_methods,id'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['integer', 'exists:sections,id'],
        ];
    }
}
