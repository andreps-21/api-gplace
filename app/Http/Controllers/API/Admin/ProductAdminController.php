<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductAdminController extends BaseController
{
    private function publicImageUrl(?string $path): ?string
    {
        $p = trim((string) $path);
        if ($p === '') {
            return null;
        }
        // Se já vier como URL absoluta, respeitar.
        if (preg_match('/^https?:\/\//i', $p)) {
            return $p;
        }

        // Caminhos guardados via `store(..., 'public')` (ex.: products/xxx.webp)
        return Storage::disk('public')->url($p);
    }

    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeOptionalBrandId(array $data): array
    {
        if (! array_key_exists('brand_id', $data)) {
            return $data;
        }
        $v = $data['brand_id'];
        if ($v === '' || $v === null || $v === false || (is_numeric($v) && (int) $v < 1)) {
            $data['brand_id'] = null;
        }

        return $data;
    }

    /**
     * Coluna «model» é NOT NULL na BD; o SPA pode omitir — usa o nome comercial truncado ou «-».
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeProductModel(array $data): array
    {
        $raw = isset($data['model']) ? trim((string) $data['model']) : '';
        if ($raw !== '') {
            $data['model'] = Str::limit($raw, 60, '');

            return $data;
        }
        $name = trim((string) ($data['commercial_name'] ?? ''));
        $data['model'] = $name !== '' ? Str::limit($name, 60, '') : '-';

        return $data;
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

    /**
     * Sugestões de NCM/CEST já usados em produtos da loja (autocomplete no formulário).
     *
     * @query string q  Prefixo numérico (1–8 para NCM, 1–20 para CEST)
     * @query string field  ncm|cest  (default: ncm)
     */
    public function fiscalSuggest(Request $request)
    {
        $storeId = $this->storeId($request);
        $field = (string) $request->query('field', 'ncm');
        $q = preg_replace('/\D+/', '', (string) $request->query('q', ''));
        if ($q === '') {
            return $this->sendResponse([]);
        }

        if ($field === 'cest') {
            $q = Str::limit($q, 20, '');
            $rows = Product::query()
                ->where('products.store_id', $storeId)
                ->whereNotNull('products.cest')
                ->where('products.cest', '!=', '')
                ->where('products.cest', 'like', $q.'%')
                ->orderBy('products.cest')
                ->orderBy('products.id')
                ->select(['products.cest', 'products.ncm'])
                ->limit(50)
                ->get();

            $out = [];
            $seen = [];
            foreach ($rows as $r) {
                $cest = (string) $r->cest;
                if (isset($seen[$cest])) {
                    continue;
                }
                $seen[$cest] = true;
                $n = $r->ncm;
                $out[] = [
                    'cest' => $cest,
                    'ncm' => $n !== null && $n !== '' ? (string) $n : null,
                ];
                if (count($out) >= 20) {
                    break;
                }
            }

            return $this->sendResponse($out);
        }

        $q = Str::limit($q, 8, '');

        $rows = Product::query()
            ->where('products.store_id', $storeId)
            ->whereNotNull('products.ncm')
            ->where('products.ncm', '!=', '')
            ->where('products.ncm', 'like', $q.'%')
            ->selectRaw('products.ncm, products.cest, COUNT(*) as use_count')
            ->groupBy('products.ncm', 'products.cest')
            ->orderBy('products.ncm')
            ->orderBy('products.cest')
            ->limit(25)
            ->get();

        $out = $rows->map(function ($r) {
            $cest = $r->cest;
            if ($cest === null || $cest === '') {
                $cest = null;
            } else {
                $cest = (string) $cest;
            }

            return [
                'ncm' => (string) $r->ncm,
                'cest' => $cest,
                'use_count' => (int) $r->use_count,
            ];
        })->all();

        return $this->sendResponse($out);
    }

    /**
     * Busca um produto da loja por código de barras, SKU, referência ou ID (PDV / venda rápida).
     */
    public function resolve(Request $request)
    {
        $storeId = $this->storeId($request);
        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return $this->sendError('Informe o código.', ['code' => ['Obrigatório.']], 422);
        }

        $base = Product::query()
            ->with(['images' => function ($q) {
                $q->orderBy('id');
            }])
            ->info()
            ->where('products.store_id', $storeId)
            ->where('products.is_enabled', true);

        $product = (clone $base)->where(function ($q) use ($code) {
            $q->where('products.sku', $code)
                ->orWhere('products.reference', $code);
            if (ctype_digit($code)) {
                $q->orWhere('products.id', (int) $code);
            }
        })->first();

        if (! $product) {
            $like = '%'.$code.'%';
            $product = (clone $base)->where(function ($q) use ($like) {
                $q->where('products.sku', 'LIKE', $like)
                    ->orWhere('products.reference', 'LIKE', $like)
                    ->orWhere('products.commercial_name', 'LIKE', $like);
            })->orderBy('products.commercial_name')->first();
        }

        if (! $product) {
            return $this->sendError('Produto não encontrado.', [], 404);
        }

        $price = (float) $product->price;
        if ((float) $product->promotion_price > 0) {
            $price = (float) $product->promotion_price;
        }

        $imageUrl = $product->images->isNotEmpty()
            ? $this->publicImageUrl($product->images->first()->name)
            : null;

        return $this->sendResponse([
            'id' => $product->id,
            'reference' => $product->reference,
            'sku' => $product->sku,
            'commercial_name' => $product->commercial_name,
            'um' => $product->um ?? 'UN',
            'price' => $price,
            'quantity_available' => (int) $product->quantity,
            'image_url' => $imageUrl,
        ]);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $data = $this->normalizeProductModel(
            $this->normalizeOptionalBrandId($this->normalizeFiscalRequestInput($request->all()))
        );
        if (! isset($data['reference']) || trim((string) $data['reference']) === '') {
            $data['reference'] = Product::getReferenceForStore($storeId);
        }
        $validator = Validator::make($data, $this->rules($request));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $product = null;
        DB::transaction(function () use ($request, $storeId, $validator, &$product) {
            $validated = $validator->validated();
            $note = $validated['stock_change_note'] ?? null;
            $inputs = Arr::except($this->normalizeMoney($validated), ['payment_methods', 'sections', 'stock_change_note']);
            $inputs['store_id'] = $storeId;
            $inputs['origin'] = $inputs['origin'] ?? 0;
            $inputs['type'] = $inputs['type'] ?? Product::PRODUCT;
            $inputs['is_grid'] = $inputs['is_grid'] ?? '0';

            $product = Product::create($inputs);

            if ($request->has('payment_methods')) {
                $product->paymentMethods()->sync($request->input('payment_methods', []));
            }
            if ($request->has('sections')) {
                $product->sections()->sync($request->input('sections', []));
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

        // Resposta mínima evita 500 na serialização (ex.: relações pesadas); a listagem usa GET /admin/products.
        return $this->sendResponse($product->fresh(), '', 201);
    }

    public function show(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::with([
            'variation',
            'images' => function ($q) {
                $q->orderBy('id');
            },
        ])
            ->info()
            ->where('products.store_id', $storeId)
            ->where('products.id', $id)
            ->firstOrFail();

        $imageUrl = $product->images->isNotEmpty()
            ? $this->publicImageUrl($product->images->first()->name)
            : null;
        $product->setAttribute('image_url', $imageUrl);

        return $this->sendResponse($product);
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::query()->where('store_id', $storeId)->findOrFail($id);

        $validator = Validator::make(
            $this->normalizeProductModel(
                $this->normalizeOptionalBrandId($this->normalizeFiscalRequestInput($request->all()))
            ),
            $this->rules($request, $product->id)
        );

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
            // Remove ficheiros físicos antes de apagar a relação.
            $old = $product->images()->get(['name'])->pluck('name')->filter()->all();
            foreach ($old as $p) {
                Storage::disk('public')->delete($p);
            }
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

    /**
     * Upload/replace imagens do produto (API admin).
     *
     * Multipart: `images[]` (1..5). Ao enviar, substitui todas as imagens atuais.
     */
    public function uploadImages(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $product = Product::query()->where('store_id', $storeId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'image', 'max:5120'], // 5MB
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $product) {
            $old = $product->images()->get(['name'])->pluck('name')->filter()->all();
            foreach ($old as $p) {
                Storage::disk('public')->delete($p);
            }
            $product->images()->delete();

            $files = $request->file('images', []);
            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }
                $upload = $file->store('products', 'public');
                $product->images()->create(['name' => $upload]);
            }
        });

        $product->load(['images' => fn ($q) => $q->orderBy('id')]);
        $imageUrl = $product->images->isNotEmpty()
            ? $this->publicImageUrl($product->images->first()->name)
            : null;
        $product->setAttribute('image_url', $imageUrl);

        return $this->sendResponse($product->fresh()->load(['images']), 'Imagens actualizadas.');
    }

    /**
     * Remove máscaras (pontos, espaços) dos códigos fiscais antes da validação.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeFiscalRequestInput(array $data): array
    {
        foreach (['ncm', 'cest', 'cfop_default', 'csosn_default', 'cst_icms_default'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $raw = $data[$key];
            if ($raw === null || $raw === '') {
                $data[$key] = null;

                continue;
            }
            $digits = preg_replace('/\D+/', '', (string) $raw);
            $data[$key] = $digits !== '' ? $digits : null;
        }

        return $data;
    }

    private function normalizeMoney(array $data): array
    {
        foreach (['price', 'promotion_price', 'invoice_price', 'discount', 'weight', 'cubic_weight', 'length', 'width', 'height'] as $key) {
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
        $tenantId = $this->tenantId($request);

        return [
            'video' => ['nullable', 'url', 'max:50'],
            'reference' => [
                'required',
                'max:15',
                Rule::unique('products', 'reference')->where(fn ($q) => $q->where('store_id', $storeId))->ignore($productId),
            ],
            'origin' => ['nullable', 'integer', 'between:0,8'],
            'ncm' => ['nullable', 'digits_between:1,8'],
            'cest' => ['nullable', 'digits_between:1,20'],
            'cfop_default' => ['nullable', 'digits:4'],
            'csosn_default' => ['nullable', 'digits:3'],
            'cst_icms_default' => ['nullable', 'digits:2'],
            'nf_number' => ['nullable', 'string', 'max:20'],
            'commercial_name' => ['required', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'description_reference' => [Rule::requiredIf($isGrid), 'nullable', 'max:60'],
            'description' => ['nullable'],
            'um_id' => ['required', 'exists:measurement_units,id'],
            'tag' => ['nullable'],
            'price' => ['required'],
            'promotion_price' => ['required'],
            'invoice_price' => ['nullable'],
            'discount' => ['nullable'],
            'payment_condition' => ['nullable', 'max:30'],
            'weight' => ['nullable'],
            'width' => ['required'],
            'height' => ['required'],
            'length' => ['required'],
            'cubic_weight' => ['nullable'],
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
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
                'nullable',
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
