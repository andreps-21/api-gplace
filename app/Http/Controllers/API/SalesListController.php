<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

/**
 * Lista de vendas — formato paginado esperado pelo dashboard Next.js.
 * Resposta vazia até existir recurso completo de vendas na API v1.
 */
class SalesListController extends BaseController
{
    public function __invoke(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(10000, max(1, (int) $request->query('per_page', 15)));

        return $this->sendResponse([
            'current_page' => $page,
            'data' => [],
            'first_page_url' => null,
            'from' => null,
            'last_page' => 1,
            'last_page_url' => null,
            'links' => [],
            'next_page_url' => null,
            'path' => $request->url(),
            'per_page' => $perPage,
            'prev_page_url' => null,
            'to' => null,
            'total' => 0,
        ]);
    }
}
