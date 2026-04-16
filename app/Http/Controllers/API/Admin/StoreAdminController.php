<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Store;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Store::person()
            ->with(['tenant.people'])
            ->where('stores.tenant_id', $tenantId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('people.name', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('people.name');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $person = Person::where('nif', $request->nif)->first();
        $validator = Validator::make($request->all(), $this->rules($person->id ?? null));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $tenantId = $this->tenantId($request);

        $store = null;
        DB::transaction(function () use ($request, $tenantId, &$store) {
            $inputs = $request->all();
            $inputs['tenant_id'] = $tenantId;
            $inputs['app_token'] = $inputs['app_token'] ?? uniqid('app_', true);

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                collect($inputs)->only((new Person())->getFillable())->all()
            );

            $inputs['person_id'] = $person->id;
            $store = Store::updateOrCreate(
                ['person_id' => $person->id],
                collect($inputs)->only((new Store())->getFillable())->all()
            );

            if ($request->filled('paymentMethods')) {
                $store->paymentMethods()->sync($request->paymentMethods);
            }
        });

        return $this->sendResponse($store->load('paymentMethods'), '', 201);
    }

    public function show(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Store::person()
            ->with('paymentMethods')
            ->where('stores.tenant_id', $tenantId)
            ->findOrFail($id);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Store::query()->where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            $item->fill(collect($inputs)->only($item->getFillable())->all())->save();
            $person = Person::find($item->person_id);
            $person->fill(collect($inputs)->only($person->getFillable())->all())->save();

            if ($request->has('paymentMethods')) {
                $item->paymentMethods()->sync($request->paymentMethods ?? []);
            }
        });

        return $this->sendResponse($item->fresh()->load('paymentMethods'));
    }

    public function destroy(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Store::query()->where('tenant_id', $tenantId)->findOrFail($id);

        try {
            $item->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $personId = null): array
    {
        return [
            'name' => ['required', 'max:30'],
            'formal_name' => ['required', 'max:60'],
            'nif' => ['required', 'max:20', new CpfCnpj, Rule::unique('people', 'nif')->ignore($personId)],
            'city_id' => ['required', 'exists:cities,id'],
            'email' => ['required', 'max:89', Rule::unique('people', 'email')->ignore($personId)],
            'phone' => ['required', 'max:15'],
            'street' => ['required', 'max:120'],
            'status' => ['required'],
            'paymentMethods' => ['sometimes', 'array'],
            'paymentMethods.*' => ['integer', 'exists:payment_methods,id'],
        ];
    }
}
