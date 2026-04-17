<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
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
            'image' => $request->hasFile('image') ? $request->file('image')->store('brands', 'public') : null,
        ]);

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

        $inputs = $request->all();
        $inputs['tenant_id'] = $request->get('store')['tenant_id'];
        $brand->fill($inputs)->save();

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
            $brand->delete();
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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
