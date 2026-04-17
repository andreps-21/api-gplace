<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Product;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Agregações reais a partir de pedidos e itens (escopo da loja do header `app`).
 */
class DashboardController extends BaseController
{
    public function stats(Request $request)
    {
        $store = $request->get('store');
        $storeId = (int) $store['id'];

        $sellerId = $request->query('seller_id');
        $sellerId = $sellerId !== null && $sellerId !== '' ? (int) $sellerId : null;

        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();

        $monthQuery = Order::query()
            ->where('store_id', $storeId)
            ->where('status', '!=', 8)
            ->whereDate('purchase_date', '>=', $monthStart)
            ->whereDate('purchase_date', '<=', $monthEnd)
            ->when($sellerId, fn ($q) => $q->where('salesman_id', $sellerId));

        $faturamentoMes = (float) (clone $monthQuery)->sum(DB::raw('COALESCE(orders.total, orders.vl_amount)'));

        $vendedoresAtivos = Salesman::query()
            ->where('status', 1)
            ->whereHas('stores', fn ($q) => $q->where('stores.id', $storeId))
            ->when($sellerId, fn ($q) => $q->where('salesmen.id', $sellerId))
            ->count();

        $aniversariantes = Salesman::query()
            ->select([
                'salesmen.id',
                'salesmen.birth_date',
                'people.name',
                'people.email',
            ])
            ->join('people', 'people.id', '=', 'salesmen.person_id')
            ->whereHas('stores', fn ($q) => $q->where('stores.id', $storeId))
            ->whereNotNull('salesmen.birth_date')
            ->whereMonth('salesmen.birth_date', $now->month)
            ->get()
            ->map(function ($row) use ($store) {
                $bd = $row->birth_date ? Carbon::parse($row->birth_date) : null;

                return [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'birthdate' => $bd ? $bd->toDateString() : null,
                    'day_of_month' => $bd ? (int) $bd->day : null,
                    'establishment_name' => $store['name'] ?? null,
                ];
            })
            ->values()
            ->all();

        return $this->sendResponse([
            'vendedores_ativos' => $vendedoresAtivos,
            'faturamento_mes_atual' => $faturamentoMes,
            'aniversariantes_do_mes' => $aniversariantes,
        ]);
    }

    /**
     * Contagem de pedidos por mês (1–12) e por ano, para gráfico comparativo (escopo da loja).
     *
     * @queryParam years_back int Anos anteriores além do ano corrente (0 = só ano atual; default 2).
     */
    public function ordersYearly(Request $request)
    {
        $storeId = (int) $request->get('store')['id'];
        $sellerId = $request->query('seller_id');
        $sellerId = $sellerId !== null && $sellerId !== '' ? (int) $sellerId : null;

        $yearsBack = (int) $request->query('years_back', 2);
        $yearsBack = max(0, min($yearsBack, 10));

        $currentYear = (int) Carbon::now()->year;
        $fromYear = $currentYear - $yearsBack;
        $anos = range($fromYear, $currentYear);

        $rows = DB::table('orders')
            ->where('store_id', $storeId)
            ->where('status', '!=', 8)
            ->whereRaw('YEAR(purchase_date) BETWEEN ? AND ?', [$fromYear, $currentYear])
            ->when($sellerId, fn ($q) => $q->where('salesman_id', $sellerId))
            ->selectRaw('YEAR(purchase_date) as y, MONTH(purchase_date) as m, COUNT(*) as c')
            ->groupByRaw('YEAR(purchase_date), MONTH(purchase_date)')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $y = (int) $row->y;
            $m = (int) $row->m;
            if (! isset($counts[$y])) {
                $counts[$y] = [];
            }
            $counts[$y][$m] = (int) $row->c;
        }

        $mesLabels = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $totais = [];
            foreach ($anos as $y) {
                $totais[(string) $y] = (int) ($counts[$y][$m] ?? 0);
            }
            $meses[] = [
                'mes' => $m,
                'label' => $mesLabels[$m],
                'totais' => $totais,
            ];
        }

        return $this->sendResponse([
            'anos' => array_values($anos),
            'meses' => $meses,
        ]);
    }

    public function faturamento(Request $request)
    {
        $storeId = (int) $request->get('store')['id'];
        $sellerId = $request->query('seller_id');
        $sellerId = $sellerId !== null && $sellerId !== '' ? (int) $sellerId : null;

        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $curr = $this->aggregateByCategory($storeId, $dateFrom, $dateTo, $sellerId);

        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $days = $start->diffInDays($end) + 1;
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays(max(0, $days - 1));

        $prev = $this->aggregateByCategory(
            $storeId,
            $prevStart->toDateString(),
            $prevEnd->toDateString(),
            $sellerId
        );

        $keys = ['servico', 'chip', 'aparelho', 'acessorio'];
        $variacoes = [];
        foreach ($keys as $k) {
            $c = $curr[$k]['valor'] ?? 0;
            $p = $prev[$k]['valor'] ?? 0;
            if ($p > 0) {
                $variacoes[$k] = round((($c - $p) / $p) * 100, 2);
            } else {
                $variacoes[$k] = $c > 0 ? 100.0 : 0.0;
            }
        }

        return $this->sendResponse([
            'faturamento' => [
                'servico' => $curr['servico']['valor'],
                'chip' => $curr['chip']['valor'],
                'aparelho' => $curr['aparelho']['valor'],
                'acessorio' => $curr['acessorio']['valor'],
            ],
            'quantidade_vendas' => [
                'servico' => $curr['servico']['qtd'],
                'chip' => $curr['chip']['qtd'],
                'aparelho' => $curr['aparelho']['qtd'],
                'acessorio' => $curr['acessorio']['qtd'],
            ],
            'variacoes' => $variacoes,
        ]);
    }

    /**
     * Categorias: serviço = produto tipo S; produtos físicos (P) por type_sale (2 chip, 3 acessório; restante aparelho).
     *
     * @return array<string, array{valor: float, qtd: int}>
     */
    private function aggregateByCategory(int $storeId, string $dateFrom, string $dateTo, ?int $sellerId): array
    {
        $s = Product::SERVICE;
        $p = Product::PRODUCT;

        $row = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', '!=', 8)
            ->whereDate('orders.purchase_date', '>=', $dateFrom)
            ->whereDate('orders.purchase_date', '<=', $dateTo)
            ->when($sellerId, fn ($q) => $q->where('orders.salesman_id', $sellerId))
            ->selectRaw("
                SUM(CASE WHEN products.type = ? THEN order_items.total ELSE 0 END) as servico_val,
                SUM(CASE WHEN products.type = ? AND products.type_sale = 2 THEN order_items.total ELSE 0 END) as chip_val,
                SUM(CASE WHEN products.type = ? AND products.type_sale = 3 THEN order_items.total ELSE 0 END) as acessorio_val,
                SUM(CASE WHEN products.type = ? AND (products.type_sale IS NULL OR products.type_sale NOT IN (2, 3)) THEN order_items.total ELSE 0 END) as aparelho_val,
                SUM(CASE WHEN products.type = ? THEN 1 ELSE 0 END) as servico_qtd,
                SUM(CASE WHEN products.type = ? AND products.type_sale = 2 THEN 1 ELSE 0 END) as chip_qtd,
                SUM(CASE WHEN products.type = ? AND products.type_sale = 3 THEN 1 ELSE 0 END) as acessorio_qtd,
                SUM(CASE WHEN products.type = ? AND (products.type_sale IS NULL OR products.type_sale NOT IN (2, 3)) THEN 1 ELSE 0 END) as aparelho_qtd
            ", [$s, $p, $p, $p, $s, $p, $p, $p])
            ->first();

        if (! $row) {
            return [
                'servico' => ['valor' => 0.0, 'qtd' => 0],
                'chip' => ['valor' => 0.0, 'qtd' => 0],
                'aparelho' => ['valor' => 0.0, 'qtd' => 0],
                'acessorio' => ['valor' => 0.0, 'qtd' => 0],
            ];
        }

        return [
            'servico' => ['valor' => (float) ($row->servico_val ?? 0), 'qtd' => (int) ($row->servico_qtd ?? 0)],
            'chip' => ['valor' => (float) ($row->chip_val ?? 0), 'qtd' => (int) ($row->chip_qtd ?? 0)],
            'aparelho' => ['valor' => (float) ($row->aparelho_val ?? 0), 'qtd' => (int) ($row->aparelho_qtd ?? 0)],
            'acessorio' => ['valor' => (float) ($row->acessorio_val ?? 0), 'qtd' => (int) ($row->acessorio_qtd ?? 0)],
        ];
    }
}
