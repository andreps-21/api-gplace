<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Freight;
use Illuminate\Http\Request;

class FreightAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->get('store')['id'];
    }

    public function index(Request $request)
    {
        $query = Freight::query()
            ->city()
            ->where('freights.store_id', $this->storeId($request))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(fn ($qq) => $qq
                    ->where('freights.name', 'like', $search)
                    ->orWhere('freights.description', 'like', $search)
                    ->orWhere('cities.title', 'like', $search));
            })
            ->orderBy('freights.name');

        return $this->sendResponse(
            $request->boolean('all') ? $query->get() : $query->paginate((int) $request->get('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['store_id'] = $this->storeId($request);
        $item = Freight::create($data);

        return $this->sendResponse($item, 'Registro criado com sucesso.', 201);
    }

    public function show(Request $request, int $id)
    {
        $item = Freight::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $item = Freight::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);
        $item->fill($request->validate($this->rules()))->save();

        return $this->sendResponse($item->fresh(), 'Registro atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id)
    {
        Freight::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id)
            ->delete();

        return $this->sendResponse([], 'Registro deletado com sucesso.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'is_enabled' => ['required', 'boolean'],
            'description' => ['required', 'string', 'max:60'],
            'zip_code_start' => ['required', 'string', 'max:9'],
            'zip_code_end' => ['required', 'string', 'max:9'],
            'notes' => ['nullable', 'string', 'max:150'],
            'percentage' => ['required', 'numeric', 'min:0'],
            'value_freight_fix' => ['nullable', 'numeric', 'min:0'],
            'free_shipping_sales' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
