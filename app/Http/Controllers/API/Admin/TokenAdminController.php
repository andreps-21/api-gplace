<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TokenAdminController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = min(100, max(5, (int) $request->query('per_page', 10)));

        $paginator = Token::query()
            ->orderBy('description')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (Token $t) {
            $plain = $t->access_token;
            $t->setAttribute('access_token_preview', $plain ? (substr($plain, 0, 8).'…') : null);
            $t->makeHidden(['access_token']);

            return $t;
        });

        return $this->sendResponse($paginator);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        if (empty($data['store_id']) && $request->attributes->has('store')) {
            $data['store_id'] = (int) $request->attributes->get('store')['id'];
        }
        $data['access_token'] = hash('sha256', Str::random(40));
        $data['expires_at'] = now()->addYear();

        $item = Token::create($data);

        $plain = $item->access_token;
        $item->setAttribute('access_token_plain', $plain);
        $item->setAttribute('access_token_preview', $plain ? (substr($plain, 0, 8).'…') : null);
        $item->makeHidden(['access_token']);

        return $this->sendResponse($item, '', 201);
    }

    public function show(int $id)
    {
        $item = Token::with('store.people')->findOrFail($id);
        $plain = $item->access_token;
        $item->setAttribute('access_token_preview', $plain ? (substr($plain, 0, 8).'…') : null);
        $item->makeHidden(['access_token']);

        return $this->sendResponse($item);
    }

    public function update(Request $request, int $id)
    {
        $item = Token::findOrFail($id);
        $validator = Validator::make($request->all(), $this->rules($item->id));

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();
        if (empty($data['store_id'])) {
            $data['store_id'] = $item->store_id;
        }
        $item->fill($data);
        if ($request->boolean('regenerate_token')) {
            $item->access_token = hash('sha256', Str::random(40));
            $item->expires_at = now()->addYear();
        }
        $item->save();

        $plain = $item->access_token;
        if ($request->boolean('regenerate_token')) {
            $item->setAttribute('access_token_plain', $plain);
        }
        $item->setAttribute('access_token_preview', $plain ? (substr($plain, 0, 8).'…') : null);
        $item->makeHidden(['access_token']);

        return $this->sendResponse($item);
    }

    public function destroy(int $id)
    {
        $item = Token::findOrFail($id);

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
            'description' => ['required', 'max:50', Rule::unique('tokens', 'description')->ignore($primaryKey)],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ];
    }
}
