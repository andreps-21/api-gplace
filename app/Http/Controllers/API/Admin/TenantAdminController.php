<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TenantAdminController extends BaseController
{
    private function tenantId(Request $request): int
    {
        return (int) $request->attributes->get('store')['tenant_id'];
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Tenant::person()
            ->where('tenants.id', $tenantId)
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
        $tenantId = $this->tenantId($request);
        if ((int) $id !== $tenantId) {
            abort(404);
        }

        return $this->sendResponse(Tenant::person()->where('tenants.id', $tenantId)->firstOrFail());
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->tenantId($request);
        if ((int) $id !== $tenantId) {
            abort(404);
        }
        $item = Tenant::query()->where('id', $tenantId)->firstOrFail();

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
            'due_date' => ['required', 'date'],
            'due_day' => ['required', 'integer'],
            'value' => ['required', 'numeric'],
            'signature' => ['required', 'integer'],
        ];
    }
}
