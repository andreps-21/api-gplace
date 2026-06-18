<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StateAdminController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 25)));

        $query = State::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where('title', 'LIKE', "%{$s}%")
                    ->orWhere('letter', 'LIKE', "%{$s}%");
            })
            ->orderBy('title');

        return $this->sendResponse($request->has('page') ? $query->paginate($perPage) : $query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $state = new State();
        $state->title = $request->input('title');
        $state->letter = strtoupper((string) $request->input('letter'));
        $state->save();

        return $this->sendResponse($state, 'Estado criado.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(State::query()->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $state = State::query()->findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($state->id));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $state->title = $request->input('title');
        $state->letter = strtoupper((string) $request->input('letter'));
        $state->save();

        return $this->sendResponse($state->fresh());
    }

    public function destroy(int $id)
    {
        $state = State::query()->findOrFail($id);
        try {
            $state->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a cidades.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $primaryKey = null): array
    {
        return [
            'title' => ['required', 'max:80'],
            'letter' => ['required', 'size:2', Rule::unique('states', 'letter')->ignore($primaryKey)],
        ];
    }
}
