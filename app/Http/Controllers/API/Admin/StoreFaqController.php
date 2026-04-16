<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreFaqController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->attributes->get('store')['id'];
    }

    public function index(Request $request)
    {
        $storeId = $this->storeId($request);
        $perPage = min(100, max(5, (int) $request->query('per_page', 10)));

        return $this->sendResponse(
            Faq::query()->where('store_id', $storeId)->orderBy('question')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        $data['store_id'] = $this->storeId($request);
        $item = Faq::create($data);

        return $this->sendResponse($item, '', 201);
    }

    public function show(Request $request, int $id)
    {
        $item = Faq::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $item = Faq::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $item->fill($validator->validated())->save();

        return $this->sendResponse($item);
    }

    public function destroy(Request $request, int $id)
    {
        $item = Faq::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

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
            'question' => ['required', 'max:40'],
            'answer' => ['required'],
            'url' => ['nullable', 'url'],
            'is_enabled' => ['required'],
            'position' => ['nullable', 'integer'],
        ];
    }
}
