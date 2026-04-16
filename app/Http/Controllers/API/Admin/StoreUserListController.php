<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Usuários ligados à loja do header (equivalente ao filtro do Blade users.index com sessão de loja).
 */
class StoreUserListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $store = $request->attributes->get('store');
        $storeId = (int) $store['id'];
        $perPage = min(100, max(5, (int) $request->query('per_page', 15)));

        $query = User::person()
            ->whereHas('stores', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where(function ($query) use ($s) {
                    $query->where('users.name', 'LIKE', "%{$s}%")
                        ->orWhere('users.email', 'LIKE', "%{$s}%")
                        ->orWhere('people.nif', 'LIKE', "%{$s}%");
                });
            })
            ->orderBy('users.name');

        return $this->sendResponse($query->paginate($perPage));
    }
}
