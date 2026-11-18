<?php

namespace App\Soho\Http\Controllers\Api\Report;

use App\Soho\Models\Inventory;
use App\Soho\Models\OrderItem;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 报表API控制器
 */
class ReportController extends Controller
{
    /**
     * 获取销售报表
     */
    public function sales(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shop_id' => 'integer|exists:soho_shops,id',
            'platform' => 'string|in:taobao,jd,pinduoduo,amazon',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = PlatformOrder::where('order_created_at', '>=', $startDate)
            ->where('order_created_at', '<=', $endDate . ' 23:59:59')
            ->where('order_status', '!=', 'cancelled');

        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($request->platform) {
            $query->where('platform', $request->platform);
        }

        // 按日汇总
        $dailyStats = (clone $query)
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(pay_amount) as pay_amount'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 按平台汇总
        $platformStats = (clone $query)
            ->select(
                'platform',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(pay_amount) as pay_amount'),
            )
            ->groupBy('platform')
            ->get();

        // 按店铺汇总
        $shopStats = (clone $query)
            ->select(
                'shop_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(pay_amount) as pay_amount'),
            )
            ->groupBy('shop_id')
            ->get();

        // 汇总
        $summary = (clone $query)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(pay_amount) as total_pay_amount'),
                DB::raw('AVG(pay_amount) as avg_order_amount'),
            )
            ->first();

        return response()->json([
            'data' => [
                'summary' => $summary,
                'daily' => $dailyStats,
                'by_platform' => $platformStats,
                'by_shop' => $shopStats,
            ],
        ]);
    }

    /**
     * 获取库存报表
     */
    public function inventory(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'integer|exists:soho_warehouses,id',
        ]);

        $query = Inventory::with(['warehouse', 'sku.product']);

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // 库存概览
        $overview = (clone $query)
            ->select(
                DB::raw('COUNT(*) as sku_count'),
                DB::raw('SUM(actual_quantity) as total_stock'),
                DB::raw('SUM(locked_quantity) as total_locked'),
                DB::raw('SUM(in_transit_quantity) as total_in_transit'),
                DB::raw('SUM(damaged_quantity) as total_damaged'),
                DB::raw('SUM(return_pending_quantity) as total_return_pending'),
            )
            ->first();

        // 库存预警（实际库存 <= 安全库存）
        $lowStockItems = (clone $query)
            ->whereColumn('actual_quantity', '<=', 'safety_quantity')
            ->where('safety_quantity', '>', 0)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'sku_code' => $item->sku->sku_code ?? '-',
                'product_name' => $item->sku->product->name ?? '-',
                'warehouse_name' => $item->warehouse->name ?? '-',
                'actual_quantity' => $item->actual_quantity,
                'safety_quantity' => $item->safety_quantity,
                'deficit' => $item->safety_quantity - $item->actual_quantity,
            ]);

        // 按仓库汇总
        $warehouseStats = Inventory::select(
            'warehouse_id',
            DB::raw('COUNT(*) as sku_count'),
            DB::raw('SUM(actual_quantity) as total_stock'),
            DB::raw('SUM(actual_quantity - locked_quantity) as available_stock'),
        )
            ->groupBy('warehouse_id')
            ->get();

        $warehouseNames = \App\Soho\Models\Warehouse::whereIn('id', $warehouseStats->pluck('warehouse_id')->unique())
            ->pluck('name', 'id');

        $warehouseStats->each(function ($stat) use ($warehouseNames) {
            $stat->warehouse_name = $warehouseNames[$stat->warehouse_id] ?? '-';
        });

        return response()->json([
            'data' => [
                'overview' => $overview,
                'low_stock_items' => $lowStockItems,
                'by_warehouse' => $warehouseStats,
            ],
        ]);
    }

    /**
     * 获取利润报表
     */
    public function profit(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shop_id' => 'integer|exists:soho_shops,id',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = OrderItem::whereHas('platformOrder', function ($q) use ($startDate, $endDate, $request) {
            $q->where('order_created_at', '>=', $startDate)
                ->where('order_created_at', '<=', $endDate . ' 23:59:59')
                ->where('order_status', '!=', 'cancelled');

            if ($request->shop_id) {
                $q->where('shop_id', $request->shop_id);
            }
        });

        // 按日汇总利润
        $dailyProfit = (clone $query)
            ->join('soho_platform_orders', 'order_items.order_id', '=', 'soho_platform_orders.id')
            ->select(
                DB::raw('DATE(soho_platform_orders.order_created_at) as date'),
                DB::raw('SUM(soho_order_items.quantity * soho_order_items.unit_price) as revenue'),
                DB::raw('SUM(soho_order_items.quantity * soho_order_items.cost_price) as cost'),
                DB::raw('SUM(soho_order_items.profit_amount) as profit'),
                DB::raw('SUM(soho_order_items.quantity) as sold_quantity'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 按商品汇总利润
        $productProfit = (clone $query)
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as sold_quantity'),
                DB::raw('SUM(quantity * unit_price) as revenue'),
                DB::raw('SUM(quantity * cost_price) as cost'),
                DB::raw('SUM(profit_amount) as profit'),
            )
            ->groupBy('product_name')
            ->orderByDesc('profit')
            ->limit(20)
            ->get();

        // 利润汇总
        $summary = (clone $query)
            ->select(
                DB::raw('SUM(quantity * unit_price) as total_revenue'),
                DB::raw('SUM(quantity * cost_price) as total_cost'),
                DB::raw('SUM(profit_amount) as total_profit'),
                DB::raw('SUM(quantity) as total_sold_quantity'),
            )
            ->first();

        $summary->profit_rate = $summary->total_revenue > 0
            ? round($summary->total_profit / $summary->total_revenue * 100, 2)
            : 0;

        return response()->json([
            'data' => [
                'summary' => $summary,
                'daily' => $dailyProfit,
                'by_product' => $productProfit,
            ],
        ]);
    }
}
