<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 库存周转分析控制器
 * 展示库存周转率、周转天数等核心库存运营指标
 */
class InventoryTurnoverController extends Controller
{
    /**
     * 库存周转分析列表
     * 按SKU维度计算库存周转率与周转天数
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 仓库筛选
        $warehouseId = $request->input('warehouse_id');

        // 获取当前租户下的仓库列表
        $warehouses = DB::table('soho_warehouses')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // 计算时间范围内的天数
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

        // 按SKU维度统计销售出库与当前库存
        $report = DB::table('soho_inventories as i')
            ->leftJoin('soho_product_skus as ps', 'i.sku_id', '=', 'ps.id')
            ->leftJoin('soho_products as p', 'ps.product_id', '=', 'p.id')
            ->leftJoin('soho_warehouses as w', 'i.warehouse_id', '=', 'w.id')
            ->leftJoin('soho_order_items as oi', function ($join) use ($tenantId, $startDate, $endDate) {
                $join->on('i.sku_id', '=', 'oi.sku_id')
                    ->where('oi.tenant_id', $tenantId)
                    ->whereBetween('oi.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->where('i.tenant_id', $tenantId)
            ->when($warehouseId, fn($q) => $q->where('i.warehouse_id', $warehouseId))
            ->select(
                'ps.id as sku_id',
                'ps.sku_code',
                'p.name as product_name',
                'w.name as warehouse_name',
                // 当前库存
                DB::raw('MAX(i.actual_quantity) as current_stock'),
                // 安全库存
                DB::raw('MAX(i.safety_quantity) as safety_stock'),
                // 期间销量
                DB::raw('COALESCE(SUM(oi.quantity), 0) as sold_quantity'),
                // 期间销售成本
                DB::raw('COALESCE(SUM(oi.quantity * oi.cost_price), 0) as cost_amount')
            )
            ->groupBy('ps.id', 'ps.sku_code', 'p.name', 'w.name')
            ->orderByDesc('sold_quantity')
            ->paginate(20);

        // 计算每个SKU的周转率与周转天数
        $report->getCollection()->transform(function ($item) use ($days) {
            // 平均库存 = (当前库存 + 安全库存) / 2（简化估算）
            $avgStock = ($item->current_stock + $item->safety_stock) / 2;
            // 库存周转率 = 销售成本 / 平均库存
            $item->turnover_rate = $avgStock > 0
                ? round($item->cost_amount / $avgStock, 2)
                : 0;
            // 库存周转天数 = 时间范围天数 / 周转率
            $item->turnover_days = $item->turnover_rate > 0
                ? round($days / $item->turnover_rate, 2)
                : 0;
            return $item;
        });

        // 汇总统计
        $stats = [
            'total_skus'      => $report->total(),
            'total_stock'     => $report->sum('current_stock'),
            'total_sold'      => $report->sum('sold_quantity'),
            'avg_turnover'    => $report->count() > 0 ? round($report->avg('turnover_rate'), 2) : 0,
            'avg_turnover_days' => $report->count() > 0 ? round($report->avg('turnover_days'), 2) : 0,
        ];

        return view('report.data.inventory-turnover', compact('report', 'stats', 'warehouses', 'startDate', 'endDate'));
    }
}
