<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Lead;
use App\Models\Person;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Lead::person()
            ->where('leads.store_id', $storeId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('people.name', 'LIKE', "%{$s}%")
                        ->orWhere('people.email', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('leads.created_at', '>=', $request->query('start_date'));
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('leads.created_at', '<=', $request->query('end_date'));
            })
            ->with(['store.people'])
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

        $storeId = $this->storeId($request);

        DB::transaction(function () use ($request, $storeId) {
            $inputs = $request->all();
            if (empty($inputs['phone']) && ! empty($inputs['cellphone'])) {
                $inputs['phone'] = $inputs['cellphone'];
            }
            $person = Person::updateOrCreate(
                ['nif' => $inputs['nif']],
                collect($inputs)->only((new Person())->getFillable())->all()
            );
            $inputs['person_id'] = $person->id;
            $inputs['store_id'] = $storeId;

            Lead::updateOrCreate(
                [
                    'person_id' => $inputs['person_id'],
                    'store_id' => $inputs['store_id'],
                ],
                collect($inputs)->only((new Lead())->getFillable())->all()
            );
        });

        $lead = Lead::person()
            ->where('leads.store_id', $storeId)
            ->where('people.nif', $request->nif)
            ->firstOrFail();

        return $this->sendResponse($lead, '', 201);
    }

    public function show(Request $request, int $id)
    {
        $item = Lead::person()
            ->where('leads.store_id', $this->storeId($request))
            ->where('leads.id', $id)
            ->firstOrFail();

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $item = Lead::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($item->person_id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            if (empty($inputs['phone']) && ! empty($inputs['cellphone'])) {
                $inputs['phone'] = $inputs['cellphone'];
            }
            $person = Person::findOrFail($item->person_id);
            $person->fill(collect($inputs)->only($person->getFillable())->all())->save();
            $item->fill(collect($inputs)->only($item->getFillable())->all())->save();
        });

        return $this->sendResponse($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $item = Lead::query()
            ->where('store_id', $this->storeId($request))
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
            'nif' => ['required', 'max:18', new CpfCnpj, Rule::unique('people', 'nif')->ignore($personId)],
            'email' => ['required', 'max:100', Rule::unique('people', 'email')->ignore($personId)],
            'name' => ['nullable', 'max:30'],
            'phone' => ['nullable', 'max:15'],
            'status' => ['nullable'],
            'observation' => ['nullable', 'string'],
        ];
    }
}
