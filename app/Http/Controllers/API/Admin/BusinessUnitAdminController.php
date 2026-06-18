<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\BusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessUnitAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));
        $tenantId = $this->tenantId($request);

        $query = BusinessUnit::query()
            ->city()
            ->where('tenant_id', $tenantId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('business_units.name', 'LIKE', "%{$s}%")
                        ->orWhere('business_units.description', 'LIKE', "%{$s}%")
                        ->orWhere('cities.title', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('business_units.name');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        $data['tenant_id'] = $this->tenantId($request);

        return $this->sendResponse(BusinessUnit::query()->create($data), 'Unidade criada.', 201);
    }

    public function show(Request $request, int $id)
    {
        return $this->sendResponse(
            BusinessUnit::query()
                ->city()
                ->where('business_units.tenant_id', $this->tenantId($request))
                ->where('business_units.id', $id)
                ->firstOrFail()
        );
    }

    public function update(Request $request, int $id)
    {
        $item = BusinessUnit::query()
            ->where('tenant_id', $this->tenantId($request))
            ->whereKey($id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated())->save();

        return $this->show($request, $item->id);
    }

    public function destroy(Request $request, int $id)
    {
        $item = BusinessUnit::query()
            ->where('tenant_id', $this->tenantId($request))
            ->whereKey($id)
            ->firstOrFail();

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
            'name' => ['required', 'max:30'],
            'description' => ['required', 'max:60'],
            'is_enabled' => ['required', 'boolean'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'zip_code_start' => ['required', 'max:10'],
            'zip_code_end' => ['required', 'max:10'],
        ];
    }
}
