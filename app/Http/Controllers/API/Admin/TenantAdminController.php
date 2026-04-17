<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    /**
     * Listagem completa (backoffice) se o utilizador puder criar ou editar contratantes;
     * caso contrário, apenas o titular da loja do header.
     */
    private function canBrowseAllTenants(Request $request): bool
    {
        $user = $request->user();

        return $user->hasPermissionTo('tenants_create', 'web')
            || $user->hasPermissionTo('tenants_edit', 'web');
    }

    private function userMayViewTenant(Request $request, int $id): bool
    {
        if ((int) $id === $this->tenantId($request)) {
            return true;
        }

        return $request->user()->can('tenants_edit')
            || $request->user()->can('tenants_create');
    }

    private function userMayUpdateTenant(Request $request, int $id): bool
    {
        if ((int) $id === $this->tenantId($request)) {
            return true;
        }

        return $request->user()->can('tenants_edit');
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Tenant::person()
            ->when(! $this->canBrowseAllTenants($request), function ($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('people.name', 'LIKE', "%{$s}%")
                        ->orWhere('people.formal_name', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('people.name');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function show(Request $request, int $id)
    {
        if (! $this->userMayViewTenant($request, $id)) {
            abort(404);
        }

        return $this->sendResponse(Tenant::person()->where('tenants.id', $id)->firstOrFail());
    }

    public function store(Request $request)
    {
        if (! $this->canBrowseAllTenants($request)) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        $this->normalizeTenantRequest($request);

        $person = Person::where('nif', $request->input('nif'))->first();
        $validator = Validator::make($request->all(), $this->rules($person?->id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();

        try {
            $tenant = DB::transaction(function () use ($request, $validated) {
                $personAttrs = collect($validated)->only((new Person())->getFillable())->all();
                $person = Person::updateOrCreate(
                    ['nif' => $validated['nif']],
                    $personAttrs
                );

                $tenantData = Arr::only($validated, (new Tenant())->getFillable());
                if ($request->has('value')) {
                    $tenantData['value'] = is_numeric($request->value)
                        ? (float) $request->value
                        : (function_exists('moeda') ? moeda($request->value) : (float) str_replace(',', '.', preg_replace('/[^\d,.-]/', '', (string) $request->value)));
                }
                $tenantData['person_id'] = $person->id;

                $tenant = Tenant::updateOrCreate(
                    ['person_id' => $person->id],
                    $tenantData
                );

                $user = User::updateOrCreate(
                    ['person_id' => $person->id],
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => bcrypt(preg_replace('/\D+/', '', (string) $validated['nif'])),
                        'is_enabled' => (int) $validated['status'] === 1,
                    ]
                );

                $role = Role::query()->with('permissions')->where('name', '=', 'contratante')->first();
                if (! $role) {
                    throw new \RuntimeException('Role «contratante» não encontrada.');
                }

                $tenant->load('people');
                $newRole = $role->replicate();
                $newRole->created_at = now();
                $newRole->updated_at = now();
                $newRole->name = 'contratante-' . Str::slug($tenant->people->name);
                $newRole->save();
                $newRole->permissions()->sync($role->permissions);
                $user->roles()->detach();
                $user->roles()->attach($newRole->id);

                return $tenant;
            });
        } catch (\Throwable $e) {
            return $this->sendError('Não foi possível criar o contratante. ' . $e->getMessage(), [], 500);
        }

        $row = Tenant::person()->where('tenants.id', $tenant->id)->firstOrFail();

        return $this->sendResponse($row, 'Contratante criado. Senha inicial: apenas os dígitos do NIF.', 201);
    }

    public function update(Request $request, int $id)
    {
        if (! $this->userMayUpdateTenant($request, $id)) {
            abort(404);
        }
        $item = Tenant::query()->where('id', $id)->firstOrFail();

        $this->normalizeTenantRequest($request);

        $validator = Validator::make($request->all(), $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($request, $item, $validated) {
            $tenantData = Arr::only($validated, (new Tenant())->getFillable());
            if ($request->has('value')) {
                $tenantData['value'] = is_numeric($request->value)
                    ? (float) $request->value
                    : (function_exists('moeda') ? moeda($request->value) : (float) str_replace(',', '.', preg_replace('/[^\d,.-]/', '', (string) $request->value)));
            }
            $item->fill($tenantData)->save();

            $person = Person::find($item->person_id);
            $person->fill(Arr::only($validated, $person->getFillable()))->save();

            $user = User::where('person_id', $item->person_id)->first();
            if ($user && isset($validated['status'])) {
                $user->is_enabled = (int) $validated['status'] === 1;
                $user->save();
            }
        });

        return $this->sendResponse($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        if (! $request->user()->can('tenants_delete')) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        if ((int) $id === $this->tenantId($request)) {
            return $this->sendError(
                'Não é possível excluir o titular associado à loja do cabeçalho app. Remova ou transfira as lojas deste tenant primeiro.',
                [],
                422
            );
        }

        $item = Tenant::query()->where('id', $id)->firstOrFail();

        try {
            $item->delete();
        } catch (\Throwable $e) {
            report($e);

            return $this->sendError(
                'Não foi possível excluir: existem registos vinculados (lojas, clientes, etc.).',
                [],
                409
            );
        }

        return $this->sendResponse(null, 'Contratante removido.');
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
            'contact_phone' => ['nullable', 'max:15'],
            'contact' => ['nullable', 'max:120'],
            'status' => ['required'],
            'dt_accession' => ['required', 'date'],
            // Coluna é string no BD; o Blade não valida como date estrito. Valor monetário vem formatado (moeda BR).
            'due_date' => ['required', 'string', 'max:32'],
            'due_day' => ['required', 'integer'],
            'value' => ['required'],
            'signature' => ['required', 'integer'],
        ];
    }

    /**
     * Alinha o payload do Next.js / SPA (camelCase, aninhamentos) ao esperado pelo backend (snake_case).
     */
    private function normalizeTenantRequest(Request $request): void
    {
        // Formulários que enviam { person: {...}, tenant: {...} }
        if ($request->filled('person') && is_array($request->input('person'))) {
            $request->merge($request->input('person'));
        }
        if ($request->filled('tenant') && is_array($request->input('tenant'))) {
            $request->merge($request->input('tenant'));
        }

        foreach (['city_id', 'cityId'] as $ck) {
            $cv = $request->input($ck);
            if (is_array($cv) && isset($cv['id'])) {
                $request->merge(['city_id' => $cv['id']]);
            }
        }

        $map = [
            'formalName' => 'formal_name',
            'cityId' => 'city_id',
            'dtAccession' => 'dt_accession',
            'adhesionDate' => 'dt_accession',
            'dueDate' => 'due_date',
            'dueDay' => 'due_day',
            'contactPhone' => 'contact_phone',
            'zipCode' => 'zip_code',
        ];

        $merged = [];
        foreach ($map as $camel => $snake) {
            if ($request->filled($camel) && ! $request->filled($snake)) {
                $merged[$snake] = $request->input($camel);
            }
        }

        if ($merged !== []) {
            $request->merge($merged);
        }

        foreach (['document', 'cpfCnpj', 'cpf_cnpj', 'taxId', 'cnpj', 'cpf'] as $altNif) {
            if ($request->filled($altNif) && ! $request->filled('nif')) {
                $request->merge(['nif' => $request->input($altNif)]);
            }
        }

        if ($request->filled('nif')) {
            $digits = preg_replace('/\D/', '', (string) $request->input('nif'));
            if ($digits !== '') {
                $request->merge(['nif' => $digits]);
            }
        }

        foreach (['dt_accession', 'due_date'] as $dateField) {
            if (! $request->filled($dateField)) {
                continue;
            }
            $v = $request->input($dateField);
            if (! is_string($v)) {
                continue;
            }
            $v = trim($v);
            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $v, $m)) {
                $request->merge([$dateField => "{$m[3]}-{$m[2]}-{$m[1]}"]);
            }
        }
    }
}
