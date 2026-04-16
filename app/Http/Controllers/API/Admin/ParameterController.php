<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ParameterController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        return $this->sendResponse(
            Parameter::query()->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item = Parameter::create($validator->validated());

        return $this->sendResponse($item, '', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(Parameter::findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $item = Parameter::findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($item->id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated())->save();

        return $this->sendResponse($item);
    }

    public function destroy(int $id)
    {
        $item = Parameter::findOrFail($id);

        try {
            $item->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(?int $primaryKey = null): array
    {
        return [
            'name' => ['required', 'max:30', Rule::unique('parameters', 'name')->ignore($primaryKey)],
            'type' => 'required',
            'value' => 'required|max:250',
            'description' => 'nullable|max:120',
        ];
    }
}
