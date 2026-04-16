<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

/**
 * Endpoints esperados pelo frontend Next.js (cards do dashboard).
 * Respostas mínimas até existir agregação real na API.
 */
class DashboardController extends BaseController
{
    public function stats(Request $request)
    {
        return $this->sendResponse([
            'vendedores_ativos' => 0,
            'faturamento_mes_atual' => 0,
            'aniversariantes_do_mes' => [],
        ]);
    }

    public function faturamento(Request $request)
    {
        return $this->sendResponse([
            'faturamento' => [
                'servico' => 0,
                'chip' => 0,
                'aparelho' => 0,
                'acessorio' => 0,
            ],
            'quantidade_vendas' => [
                'servico' => 0,
                'chip' => 0,
                'aparelho' => 0,
                'acessorio' => 0,
            ],
            'variacoes' => [
                'servico' => 0,
                'chip' => 0,
                'aparelho' => 0,
                'acessorio' => 0,
            ],
        ]);
    }
}
