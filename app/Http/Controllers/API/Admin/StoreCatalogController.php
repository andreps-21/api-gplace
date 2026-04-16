<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoreCatalogController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 10)));

        return $this->sendResponse(
            Catalog::query()->where('store_id', $storeId)->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        $data['store_id'] = $this->storeId($request);
        $item = new Catalog($data);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $item->image = $request->file('image')->store('catalogs', 'public');
        }

        $item->save();

        return $this->sendResponse($item, '', 201);
    }

    public function show(Request $request, int $id)
    {
        $item = Catalog::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $item = Catalog::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated());

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $item->image = $request->file('image')->store('catalogs', 'public');
        }

        $item->save();

        return $this->sendResponse($item);
    }

    public function destroy(Request $request, int $id)
    {
        $item = Catalog::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        try {
            $item->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'max:120'],
            'url' => ['required', 'string', 'max:500'],
            'text_email' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:120'],
            'is_enabled' => ['required'],
        ];
    }
}
