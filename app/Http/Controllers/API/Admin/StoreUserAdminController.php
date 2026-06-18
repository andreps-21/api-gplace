<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Cria utilizador + person e associa à loja do contexto (equivalente simplificado ao Blade users.store).
 */
class StoreUserAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    private function storeIdsForTenant(int $tenantId): array
    {
        return Store::query()->where('tenant_id', $tenantId)->pluck('id')->all();
    }

    public function show(Request $request, int $id)
    {
        $storeIds = $this->storeIdsForTenant($this->tenantId($request));
        $user = User::person()
            ->with(['roles:id,name,description', 'stores' => fn ($q) => $q->whereIn('stores.id', $storeIds)])
            ->where('users.id', $id)
            ->whereHas('stores', fn ($q) => $q->whereIn('stores.id', $storeIds))
            ->firstOrFail();

        return $this->sendResponse($user);
    }

    public function store(Request $request)
    {
        $storeId = $this->storeId($request);
        $tenantId = $this->tenantId($request);
        $allowedStoreIds = $this->storeIdsForTenant($tenantId);

        $existingPerson = Person::query()->where('nif', $request->input('nif'))->first();
        $personId = $existingPerson?->id;
        $existingUserId = $existingPerson
            ? User::query()->where('person_id', $existingPerson->id)->value('id')
            : null;

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:120'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($personId)],
            'city_id' => ['required', 'exists:cities,id'],
            'email' => [
                'required',
                'max:89',
                Rule::unique('people', 'email')->ignore($personId),
                Rule::unique('users', 'email')->ignore($existingUserId),
            ],
            'phone' => ['required', 'max:15'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::in($allowedStoreIds)],
            'password' => ['required', 'min:8', 'confirmed'],
            'formal_name' => ['nullable', 'max:60'],
            'street' => ['nullable', 'max:120'],
            'zip_code' => ['nullable', 'max:9'],
            'district' => ['nullable', 'max:30'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $role = Role::query()
            ->whereKey($request->integer('role_id'))
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->first();

        if (! $role) {
            return $this->sendError('Role inválida para o tenant da loja.', [], 422);
        }

        $userId = DB::transaction(function () use ($request, $storeId, $role) {
            $personAttrs = collect($request->all())
                ->only((new Person())->getFillable())
                ->all();
            $personAttrs['name'] = $request->input('name');
            $personAttrs['email'] = $request->input('email');
            $personAttrs['phone'] = $request->input('phone');

            $person = Person::updateOrCreate(
                ['nif' => $request->input('nif')],
                $personAttrs
            );

            $user = User::updateOrCreate(
                ['person_id' => $person->id],
                [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'is_enabled' => true,
                ]
            );

            $user->roles()->sync([$role->id]);
            $storeIds = $request->input('store_ids');
            $user->stores()->syncWithoutDetaching(is_array($storeIds) && $storeIds !== [] ? $storeIds : [$storeId]);

            return $user->id;
        });

        $row = User::person()->where('users.id', $userId)->firstOrFail();

        return $this->sendResponse($row, 'Utilizador criado e associado à loja.', 201);
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $allowedStoreIds = $this->storeIdsForTenant($tenantId);
        $user = User::query()
            ->whereKey($id)
            ->whereHas('stores', fn ($q) => $q->whereIn('stores.id', $allowedStoreIds))
            ->firstOrFail();

        $person = Person::query()->findOrFail($user->person_id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:120'],
            'city_id' => ['required', 'exists:cities,id'],
            'email' => [
                'required',
                'max:89',
                Rule::unique('people', 'email')->ignore($person->id),
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['required', 'max:15'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::in($allowedStoreIds)],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'formal_name' => ['nullable', 'max:60'],
            'street' => ['nullable', 'max:120'],
            'zip_code' => ['nullable', 'max:9'],
            'district' => ['nullable', 'max:30'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $role = Role::query()
            ->whereKey($request->integer('role_id'))
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->first();

        if (! $role) {
            return $this->sendError('Role inválida para o tenant da loja.', [], 422);
        }

        DB::transaction(function () use ($request, $user, $person, $role, $allowedStoreIds) {
            $personAttrs = collect($request->all())->only((new Person())->getFillable())->all();
            $personAttrs['name'] = $request->input('name');
            $personAttrs['email'] = $request->input('email');
            $personAttrs['phone'] = $request->input('phone');
            $person->fill($personAttrs)->save();

            $userAttrs = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ];
            if ($request->filled('password')) {
                $userAttrs['password'] = bcrypt($request->input('password'));
            }
            $user->fill($userAttrs)->save();
            $user->roles()->sync([$role->id]);

            $requestedStoreIds = $request->input('store_ids');
            if (is_array($requestedStoreIds)) {
                $user->stores()->detach($allowedStoreIds);
                $user->stores()->syncWithoutDetaching($requestedStoreIds);
            }
        });

        return $this->show($request, $user->id);
    }

    public function destroy(Request $request, int $id)
    {
        if ((int) $request->user()->id === $id) {
            return $this->sendError('Não é possível remover o próprio utilizador.', [], 422);
        }

        $allowedStoreIds = $this->storeIdsForTenant($this->tenantId($request));
        $user = User::query()
            ->whereKey($id)
            ->whereHas('stores', fn ($q) => $q->whereIn('stores.id', $allowedStoreIds))
            ->firstOrFail();

        try {
            $user->stores()->detach($allowedStoreIds);
            if (! $user->stores()->exists()) {
                $user->delete();
            }
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }
}
