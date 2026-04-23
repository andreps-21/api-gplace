<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Address;
use App\Models\City;
use App\Models\Customer;
use App\Models\Person;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Customer::person()
            ->whereHas('tenants', function ($que) use ($tenantId) {
                $que->where('tenants.id', $tenantId);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('people.name', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('customers.status', $request->query('status'));
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('customers.created_at', '>=', $request->query('start_date'));
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('customers.created_at', '<=', $request->query('end_date'));
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

        DB::transaction(function () use ($request, $tenantId) {
            $inputs = $request->all();

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                collect($inputs)->only((new Person())->getFillable())->all()
            );

            $inputs['person_id'] = $person->id;

            $customer = Customer::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                collect($inputs)->only((new Customer())->getFillable())->all()
            );

            User::updateOrCreate(
                ['person_id' => $person->id],
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt(preg_replace('/\D+/', '', (string) $request->nif)),
                    'is_enabled' => true,
                ]
            );

            $customer->tenants()->syncWithoutDetaching([$tenantId]);
        });

        $customer = Customer::person()
            ->where('people.nif', $request->nif)
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->firstOrFail();

        return $this->sendResponse($customer, '', 201);
    }

    /**
     * Cadastro mínimo (CPF/CNPJ, nome, telefone) para venda de balcão / quick sale.
     * Preenche endereço e e-mail padrão no servidor.
     */
    public function storeQuick(Request $request)
    {
        $nifDigits = preg_replace('/\D+/', '', (string) $request->input('nif', ''));
        if ($nifDigits === '') {
            $nifDigits = null;
        }
        $name = trim((string) $request->input('name', ''));
        $phoneDigits = preg_replace('/\D+/', '', (string) $request->input('phone', ''));
        if ($phoneDigits === '') {
            $phoneDigits = null;
        }

        $validator = Validator::make(
            [
                'nif' => $nifDigits,
                'name' => $name,
                'phone' => $phoneDigits,
            ],
            [
                'nif' => ['nullable', 'string', new CpfCnpj, Rule::unique('people', 'nif')],
                'name' => ['required', 'string', 'max:30'],
                'phone' => ['nullable', 'string', 'min:10', 'max:20'],
            ],
            [
                'nif.unique' => 'Este CPF/CNPJ já está cadastrado. Selecione o cliente na lista acima.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $tenantId = $this->tenantId($request);

        $cityId = (int) $request->input('city_id', 0);
        if ($cityId <= 0) {
            $cityId = (int) City::orderBy('id')->value('id');
        }
        if ($cityId <= 0) {
            return $this->sendError('Não há cidades cadastradas no sistema. Impossível criar cliente de balcão.', [], 422);
        }

        if (! City::query()->whereKey($cityId)->exists()) {
            return $this->sendError('Cidade inválida.', [], 422);
        }

        $tenantIdForClosure = $tenantId;
        $customerId = null;

        DB::transaction(function () use ($name, $nifDigits, $phoneDigits, $cityId, $tenantIdForClosure, &$customerId) {
            $email = $this->makeUniqueBalcaoEmail($nifDigits, $tenantIdForClosure);

            if ($nifDigits) {
                $person = Person::updateOrCreate(
                    ['nif' => $nifDigits],
                    [
                        'name' => $name,
                        'formal_name' => $name,
                        'nif' => $nifDigits,
                        'email' => $email,
                        'phone' => $phoneDigits,
                        'street' => '—',
                        'number' => 'S/N',
                        'district' => 'Balcão',
                        'zip_code' => '00000-000',
                        'city_id' => $cityId,
                    ]
                );
            } else {
                $person = Person::create([
                    'name' => $name,
                    'formal_name' => $name,
                    'nif' => null,
                    'email' => $email,
                    'phone' => $phoneDigits,
                    'street' => '—',
                    'number' => 'S/N',
                    'district' => 'Balcão',
                    'zip_code' => '00000-000',
                    'city_id' => $cityId,
                ]);
            }

            $customer = Customer::updateOrCreate(
                ['person_id' => $person->id],
                [
                    'person_id' => $person->id,
                    'origin' => 1,
                    'status' => 1,
                ]
            );

            $passwordSeed = $nifDigits ?: (string) microtime(true);
            User::updateOrCreate(
                ['person_id' => $person->id],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt($passwordSeed),
                    'is_enabled' => true,
                ]
            );

            $customer->tenants()->syncWithoutDetaching([$tenantIdForClosure]);
            $customerId = $customer->id;
        });

        $customer = Customer::person()
            ->where('customers.id', (int) $customerId)
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantIdForClosure))
            ->firstOrFail();

        return $this->sendResponse($customer, '', 201);
    }

    private function makeUniqueBalcaoEmail(?string $nifDigits, int $tenantId): string
    {
        $seed = $nifDigits ?: substr(preg_replace('/\D+/', '', (string) microtime(true)), 0, 12);
        if ($seed === '') {
            $seed = (string) random_int(10_000, 999_999);
        }
        $email = sprintf('c%s.t%d@balcao.gplace', $seed, $tenantId);
        $i = 0;
        while (Person::query()->where('email', $email)->exists() && $i < 30) {
            $email = sprintf('c%s.t%d.%d@balcao.gplace', $seed, $tenantId, ++$i);
        }

        return $email;
    }

    public function show(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Customer::person()
            ->where('customers.id', $id)
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->firstOrFail();

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Customer::query()
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->except(['nif', 'email']);
            $item->fill(collect($inputs)->only($item->getFillable())->all())->save();
            $people = Person::find($item->person_id);
            $people->fill(collect($inputs)->only($people->getFillable())->all())->save();
        });

        return $this->sendResponse($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        $item = Customer::query()
            ->whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $item->tenants()->detach();
            User::where('person_id', $item->person_id)->delete();
            Address::where('customer_id', $item->id)->delete();
            Customer::where('person_id', $item->person_id)->delete();
            Person::where('id', $item->person_id)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $personId = null): array
    {
        return [
            'state_registration' => ['nullable', 'max:25'],
            'origin' => ['required'],
            'formal_name' => ['required', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people', 'nif')->ignore($personId)],
            'name' => ['required', 'string', 'max:30'],
            'zip_code' => ['required', 'max:9'],
            'number' => ['required', 'max:10'],
            'street' => ['required', 'string', 'max:60'],
            'city_id' => ['required', 'string', 'exists:cities,id'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'district' => ['required', 'string', 'min:3', 'max:30'],
            'email' => ['required', 'max:89', Rule::unique('people', 'email')->ignore($personId)],
            'contact' => ['nullable', 'max:30'],
            'contact_phone' => ['nullable', 'max:15'],
            'contact_email' => ['nullable', 'email'],
        ];
    }
}
