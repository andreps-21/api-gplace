<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Salesman;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Estatísticas por loja (tenant) — usado pelo card "Top Lojas" no Next.js.
 */
class EstablishmentStatsController extends BaseController
{
    public function __invoke(Request $request)
    {
        $tenantId = (int) $request->get('store')['tenant_id'];
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $stores = Store::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->get();

        $stats = [];

        foreach ($stores as $store) {
            $storeRow = Store::person()
                ->where('stores.id', $store->id)
                ->first();

            $name = $storeRow->name ?? ('Loja #' . $store->id);

            $base = Order::query()
                ->where('store_id', $store->id)
                ->where('status', '!=', 8)
                ->whereDate('purchase_date', '>=', $dateFrom)
                ->whereDate('purchase_date', '<=', $dateTo);

            $totalSales = (clone $base)->count();
            $totalRevenue = (float) (clone $base)->sum(DB::raw('COALESCE(orders.total, orders.vl_amount)'));

            $activeSellers = Salesman::query()
                ->where('status', 1)
                ->whereHas('stores', function ($q) use ($store) {
                    $q->where('stores.id', $store->id);
                })
                ->count();

            $avg = $totalSales > 0 ? round($totalRevenue / $totalSales, 2) : 0.0;

            $stats[] = [
                'id' => (int) $store->id,
                'name' => $name,
                'manager' => '',
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'active_sellers' => $activeSellers,
                'sales_by_category' => [],
                'average_sale_value' => $avg,
            ];
        }

        return $this->sendResponse($stats);
    }
}
