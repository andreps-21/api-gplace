<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CityAdminController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 25)));

        $query = City::query()
            ->stateName()
            ->when($request->filled('state_id'), fn ($q) => $q->where('cities.state_id', (int) $request->query('state_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($sub) use ($s) {
                    $sub->where('cities.title', 'LIKE', "%{$s}%")
                        ->orWhere('states.title', 'LIKE', "%{$s}%")
                        ->orWhere('states.letter', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('cities.title');

        return $this->sendResponse($request->has('page') ? $query->paginate($perPage) : $query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $city = new City();
        $this->fillCity($city, $request);
        $city->save();

        return $this->sendResponse($city->fresh(), 'Cidade criada.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(City::query()->stateName()->where('cities.id', $id)->firstOrFail());
    }

    public function update(Request $request, int $id)
    {
        $city = City::query()->findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($city->id));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $this->fillCity($city, $request);
        $city->save();

        return $this->show($city->id);
    }

    public function destroy(int $id)
    {
        $city = City::query()->findOrFail($id);
        try {
            $city->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a pessoas/endereços.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function fillCity(City $city, Request $request): void
    {
        $city->title = $request->input('title');
        $city->state_id = (int) $request->input('state_id');
        $city->lat = $request->input('lat');
        $city->long = $request->input('long');
    }

    private function rules(?int $primaryKey = null): array
    {
        return [
            'title' => [
                'required',
                'max:80',
                Rule::unique('cities', 'title')
                    ->where(fn ($q) => $q->where('state_id', request('state_id')))
                    ->ignore($primaryKey),
            ],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'lat' => ['nullable', 'max:30'],
            'long' => ['nullable', 'max:30'],
        ];
    }
}
