<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $items = Warehouse::query()->where('store_id', $storeId)->orderBy('name')->get();

        return $this->sendResponse($items);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:32'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        $warehouse = DB::transaction(function () use ($storeId, $data, $isDefault) {
            if ($isDefault) {
                Warehouse::query()->where('store_id', $storeId)->update(['is_default' => false]);
            }

            return Warehouse::query()->create([
                'store_id' => $storeId,
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_default' => $isDefault,
            ]);
        });

        return $this->sendResponse($warehouse, '', 201);
    }

    public function show(Request $request, int $id)
    {
        $warehouse = Warehouse::query()
            ->where('store_id', $this->storeId($request))
            ->whereKey($id)
            ->firstOrFail();

        return $this->sendResponse($warehouse);
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $warehouse = Warehouse::query()->where('store_id', $storeId)->whereKey($id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:32'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        DB::transaction(function () use ($storeId, $warehouse, $data, $isDefault) {
            if ($isDefault) {
                Warehouse::query()
                    ->where('store_id', $storeId)
                    ->whereKeyNot($warehouse->id)
                    ->update(['is_default' => false]);
            }

            $warehouse->fill([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_default' => $isDefault,
            ])->save();
        });

        return $this->sendResponse($warehouse->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $warehouse = Warehouse::query()
            ->where('store_id', $this->storeId($request))
            ->whereKey($id)
            ->firstOrFail();

        try {
            $warehouse->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a lotes de stock.', [], 409);
        }

        return $this->sendResponse(null);
    }
}
