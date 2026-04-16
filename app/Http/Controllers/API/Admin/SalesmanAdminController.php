<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Salesman;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SalesmanAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Salesman::person()
            ->whereHas('stores', function ($que) use ($storeId) {
                $que->where('store_id', $storeId);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('people.name', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('salesmen.status', $request->query('status'));
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('salesmen.created_at', '>=', $request->query('start_date'));
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('salesmen.created_at', '<=', $request->query('end_date'));
            })
            ->orderBy('people.name');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $people = Person::where('nif', $request->nif)->first();
        $validator = Validator::make($request->all(), $this->rules($people->id ?? null));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $storeId = $this->storeId($request);

        DB::transaction(function () use ($request, $storeId) {
            $inputs = $request->all();

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                collect($inputs)->only((new Person())->getFillable())->all()
            );

            $inputs['person_id'] = $person->id;

            $salesman = Salesman::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                collect($inputs)->only((new Salesman())->getFillable())->all()
            );

            $user = User::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt(preg_replace('/\D+/', '', (string) $inputs['nif'])),
                    'is_enabled' => ($inputs['status'] ?? 1) == 1,
                ]
            );

            $salesman->stores()->syncWithoutDetaching([$storeId]);
            $user->stores()->syncWithoutDetaching([$storeId]);
            $user->assignRole('vendedor');
        });

        $row = Salesman::person()
            ->where('people.nif', $request->nif)
            ->whereHas('stores', fn ($q) => $q->where('store_id', $storeId))
            ->firstOrFail();

        return $this->sendResponse($row, '', 201);
    }

    public function show(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $item = Salesman::person()
            ->where('salesmen.id', $id)
            ->whereHas('stores', fn ($q) => $q->where('store_id', $storeId))
            ->firstOrFail();

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $item = Salesman::query()
            ->whereHas('stores', fn ($q) => $q->where('store_id', $storeId))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->except(['email', 'nif']);
            $item->fill(collect($inputs)->only($item->getFillable())->all())->save();
            $people = Person::find($item->person_id);
            $people->fill(collect($inputs)->only($people->getFillable())->all())->save();
        });

        return $this->sendResponse($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $storeId = $this->storeId($request);
        $item = Salesman::query()
            ->whereHas('stores', fn ($q) => $q->where('store_id', $storeId))
            ->findOrFail($id);

        try {
            $item->stores()->detach($storeId);
            $item->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $personId = null): array
    {
        return [
            'state_registration' => ['nullable', 'max:25'],
            'municipal_registration' => ['nullable', 'max:25'],
            'birth_date' => ['nullable', 'date'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people', 'nif')->ignore($personId)],
            'name' => ['required', 'string', 'max:30'],
            'zip_code' => ['nullable', 'string', 'max:9'],
            'street' => ['required', 'string', 'max:60'],
            'city_id' => ['required', 'string', 'exists:cities,id'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'email' => ['required', 'max:45', Rule::unique('people', 'email')->ignore($personId)],
            'status' => ['nullable'],
        ];
    }
}
