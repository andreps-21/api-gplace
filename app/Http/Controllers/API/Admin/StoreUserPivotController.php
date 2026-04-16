<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreUserPivotController extends BaseController
{
    public function attach(Request $request)
    {
        $store = $request->attributes->get('store');
        $storeId = (int) $store['id'];

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação.', $validator->errors()->toArray(), 422);
        }

        $user = User::findOrFail($request->integer('user_id'));
        $user->stores()->syncWithoutDetaching([$storeId]);

        return $this->sendResponse($user->load(['stores' => fn ($q) => $q->where('stores.id', $storeId)]));
    }

    public function detach(Request $request, int $userId)
    {
        $storeId = (int) $request->attributes->get('store')['id'];
        $user = User::findOrFail($userId);
        $user->stores()->detach($storeId);

        return $this->sendResponse(null);
    }
}
