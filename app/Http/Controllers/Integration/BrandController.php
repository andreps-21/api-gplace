<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class BrandController extends BaseController
{
    public function index(Request $request)
    {
        $tenant = $request->get('store')['tenant_id'];

        $brands = Brand::query()
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->get()
            ->append('image_url');

        return $this->sendResponse($brands);
    }

    public function store(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $v = $validator->validated();
        $brand = Brand::create([
            'name' => $v['name'],
            'is_enabled' => (bool) ($v['is_enabled'] ?? true),
            'is_public' => (bool) ($v['is_public'] ?? true),
            'tenant_id' => $request->get('store')['tenant_id'],
            'image' => $this->storeImage($request),
        ]);
        $this->forgetHomeCache($request);

        return $this->sendResponse($brand->fresh()->append('image_url'), 'Registro criado com sucesso.', 201);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->get('store')['tenant_id'];

        $brand = Brand::query()
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->where('id', $id)
            ->firstOrFail()
            ->append('image_url');

        return $this->sendResponse($brand);
    }

    public function update(Request $request, $id)
    {
        $tenant = $request->get('store')['tenant_id'];

        $brand = Brand::query()
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->where('id', $id)
            ->firstOrFail();


        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $id)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $inputs = $validator->validated();
        $inputs['tenant_id'] = $request->get('store')['tenant_id'];
        if ($request->hasFile('image')) {
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
            $inputs['image'] = $this->storeImage($request);
        }
        $brand->fill($inputs)->save();
        $this->forgetHomeCache($request);

        return $this->sendResponse([], "Registro atualizado com sucesso.");
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $request->get('store')['tenant_id'];

        $brand = Brand::query()
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->where('tenant_id', $tenant)
            ->where('id', $id)
            ->firstOrFail();

        try {
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
            $brand->delete();
            $this->forgetHomeCache($request);
            return $this->sendResponse([], "Registro deletado com sucesso.");
        } catch (\Exception $e) {
            return $this->sendError("Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.", [], 403);
        }
    }




    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => [
                'required', 'max:30', 'min:3',
                Rule::unique('brands')->where('tenant_id', $request->get('store')['tenant_id'])->ignore($primaryKey),
            ],
            'is_enabled' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return null;
        }

        return $request->file('image')->store('brands', 'public');
    }

    private function forgetHomeCache(Request $request): void
    {
        $storeId = $request->get('store')['id'] ?? null;

        if ($storeId) {
            Cache::forget("cms-home-{$storeId}");
        }
    }
}
