<?php

namespace App\Http\Controllers\API;

use App\Models\Store;
use Illuminate\Http\Request;

/**
 * Lojas do mesmo tenant (para filtros / contagem no painel).
 */
class EstablishmentListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $tenantId = (int) $request->get('store')['tenant_id'];

        $rows = Store::person()
            ->where('stores.tenant_id', $tenantId)
            ->where('stores.status', 1)
            ->orderBy('people.name')
            ->get();

        $list = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'address' => $row->street ?? '',
                'zip_code' => $row->zip_code ?? '',
                'phone' => $row->phone ?? '',
                'email' => $row->email ?? '',
                'cnpj' => '',
                'city' => '',
                'state' => '',
                'is_active' => true,
                'created_at' => '',
                'updated_at' => '',
            ];
        })->values()->all();

        return $this->sendResponse($list);
    }
}
