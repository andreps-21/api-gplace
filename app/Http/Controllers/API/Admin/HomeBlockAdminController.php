<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\HomeBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HomeBlockAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $blocks = HomeBlock::query()
            ->with('items')
            ->where('store_id', $this->storeId($request))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->sendResponse($blocks);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $block = DB::transaction(function () use ($request, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);
            $data['store_id'] = $this->storeId($request);

            $block = HomeBlock::create($data);
            $this->syncItems($block, $items);

            return $block->fresh('items');
        });
        $this->forgetHomeCache($this->storeId($request));

        return $this->sendResponse($block, 'Bloco criado com sucesso.');
    }

    public function show(Request $request, int $id)
    {
        $block = HomeBlock::query()
            ->with('items')
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        return $this->sendResponse($block);
    }

    public function update(Request $request, int $id)
    {
        $block = HomeBlock::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $data = $request->validate($this->rules());

        $block = DB::transaction(function () use ($block, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $block->fill($data)->save();
            $this->syncItems($block, $items);

            return $block->fresh('items');
        });
        $this->forgetHomeCache($this->storeId($request));

        return $this->sendResponse($block, 'Bloco atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id)
    {
        $block = HomeBlock::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $block->delete();
        $this->forgetHomeCache($this->storeId($request));

        return $this->sendResponse(null, 'Bloco removido com sucesso.');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(HomeBlock::types())],
            'is_enabled' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.item_id' => ['required', 'integer', 'min:1'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function syncItems(HomeBlock $block, array $items): void
    {
        $block->items()->delete();

        foreach (array_values($items) as $index => $item) {
            $block->items()->create([
                'item_id' => (int) $item['item_id'],
                'sort_order' => (int) ($item['sort_order'] ?? $index),
            ]);
        }
    }

    private function forgetHomeCache(int $storeId): void
    {
        Cache::forget("cms-home-{$storeId}");
    }
}
