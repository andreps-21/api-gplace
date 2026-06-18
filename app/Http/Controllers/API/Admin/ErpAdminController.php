<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Erp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ErpAdminController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = Erp::query()
            ->when($request->filled('search'), fn ($q) => $q->where('description', 'LIKE', '%'.$request->query('search').'%'))
            ->orderBy('description');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        return $this->sendResponse(Erp::query()->create($validator->validated()), 'ERP criado.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(Erp::query()->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $item = Erp::query()->findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated())->save();

        return $this->sendResponse($item->fresh());
    }

    public function destroy(int $id)
    {
        $item = Erp::query()->findOrFail($id);
        try {
            $item->delete();
        } catch (\Throwable $e) {
            return $this->sendError('Registo vinculado a outra tabela.', [], 409);
        }

        return $this->sendResponse(null);
    }

    private function rules(): array
    {
        return [
            'description' => ['required', 'max:40'],
            'status' => ['required', 'boolean'],
        ];
    }
}
