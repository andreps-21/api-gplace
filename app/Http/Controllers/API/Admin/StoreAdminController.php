<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Store;
use App\Models\Tenant;
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

    /**
     * Mesma regra que TenantAdminController: listar/gerir vários titulares.
     */
    private function canBrowseAllTenants(Request $request): bool
    {
        $user = $request->user();

        return $user->hasPermissionTo('tenants_create', 'web')
            || $user->hasPermissionTo('tenants_edit', 'web');
    }

    /**
     * Titular da nova loja: cabeçalho «app», ou tenant_id no pedido se o utilizador puder gerir contratantes.
     */
    private function resolveTargetTenantIdForStore(Request $request): int
    {
        $headerTenantId = $this->tenantId($request);

        if (! $this->canBrowseAllTenants($request)) {
            return $headerTenantId;
        }

        $requested = $request->input('tenant_id');
        if ($requested === null || $requested === '') {
            return $headerTenantId;
        }

        $id = (int) $requested;
        if ($id < 1) {
            return $headerTenantId;
        }

        return Tenant::query()->whereKey($id)->exists() ? $id : $headerTenantId;
    }

    /**
     * Ao editar: mantém o titular actual se não enviar tenant_id; se enviar e tiver permissão, altera.
     */
    private function resolveTenantIdForStoreUpdate(Request $request, Store $store): int
    {
        if (! $this->canBrowseAllTenants($request)) {
            return (int) $store->tenant_id;
        }

        $requested = $request->input('tenant_id');
        if ($requested === null || $requested === '') {
            return (int) $store->tenant_id;
        }

        $id = (int) $requested;
        if ($id < 1) {
            return (int) $store->tenant_id;
        }

        return Tenant::query()->whereKey($id)->exists() ? $id : (int) $store->tenant_id;
    }

    public function index(Request $request)
    {
        $headerTenantId = $this->tenantId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Store::person()
            ->with(['tenant.people'])
            ->when(! $this->canBrowseAllTenants($request), function ($q) use ($headerTenantId) {
                $q->where('stores.tenant_id', $headerTenantId);
            })
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

        $tenantId = $this->resolveTargetTenantIdForStore($request);

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
        $headerTenantId = $this->tenantId($request);
        $query = Store::person()
            ->with('paymentMethods')
            ->when(! $this->canBrowseAllTenants($request), function ($q) use ($headerTenantId) {
                $q->where('stores.tenant_id', $headerTenantId);
            });

        $item = $query->findOrFail($id);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $headerTenantId = $this->tenantId($request);
        $item = Store::query()
            ->when(! $this->canBrowseAllTenants($request), function ($q) use ($headerTenantId) {
                $q->where('tenant_id', $headerTenantId);
            })
            ->findOrFail($id);

        $dataForValidation = $request->all();
        if (! $this->canBrowseAllTenants($request)) {
            unset($dataForValidation['tenant_id']);
        }

        $validator = Validator::make($dataForValidation, $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            if ($this->canBrowseAllTenants($request)) {
                $inputs['tenant_id'] = $this->resolveTenantIdForStoreUpdate($request, $item);
            } else {
                unset($inputs['tenant_id']);
            }

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
        $headerTenantId = $this->tenantId($request);
        $item = Store::query()
            ->when(! $this->canBrowseAllTenants($request), function ($q) use ($headerTenantId) {
                $q->where('tenant_id', $headerTenantId);
            })
            ->findOrFail($id);

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
            'tenant_id' => ['sometimes', 'nullable', 'integer', Rule::exists('tenants', 'id')],
        ];
    }
}
