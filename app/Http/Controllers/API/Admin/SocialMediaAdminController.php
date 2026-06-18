<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialMediaAdminController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = SocialMedia::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where('description', 'LIKE', "%{$s}%")
                    ->orWhere('code', 'LIKE', "%{$s}%");
            })
            ->orderBy('description');

        return $this->sendResponse($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        return $this->sendResponse(SocialMedia::query()->create($validator->validated()), 'Rede social criada.', 201);
    }

    public function show(int $id)
    {
        return $this->sendResponse(SocialMedia::query()->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $item = SocialMedia::query()->findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($item->id));
        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated())->save();

        return $this->sendResponse($item->fresh());
    }

    public function destroy(int $id)
    {
        $item = SocialMedia::query()->findOrFail($id);
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
            'icon' => ['nullable', 'max:255'],
            'description' => ['required', 'max:30'],
            'code' => ['required', 'max:30', Rule::unique('social_media', 'code')->ignore($primaryKey)],
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}
