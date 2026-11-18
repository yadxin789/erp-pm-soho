<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 发货及时率报表控制器
 * 展示各店铺/仓库的发货及时率，支持时间范围筛选
 */
class ShipOnTimeController extends Controller
{
    /**
     * 发货及时率列表
     * 按店铺维度统计24小时内发货的及时率，支持时间范围筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选参数（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 店铺筛选参数
        $shopId = $request->input('shop_id');

        // 获取当前租户下的店铺列表（用于筛选下拉框）
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 按店铺统计发货及时率
        $report = DB::table('soho_platform_orders as o')
            ->leftJoin('soho_shops as s', 'o.shop_id', '=', 's.id')
            ->where('o.tenant_id', $tenantId)
            ->whereNotNull('o.ship_time')
            ->whereNotNull('o.order_created_at')
            ->when($shopId, fn($q) => $q->where('o.shop_id', $shopId))
            ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                's.shop_name',
                'o.shop_id',
                DB::raw('COUNT(*) as shipped_count'),
                // 24小时内发货视为及时（SQLite 使用 julianday 计算天数差）
                DB::raw('SUM(CASE WHEN DATEDIFF(o.ship_time, o.order_created_at) <= 1 THEN 1 ELSE 0 END) as on_time_count'),
                // 平均发货时长（小时）
                DB::raw('AVG((DATEDIFF(o.ship_time, o.order_created_at)) * 24) as avg_ship_hours')
            )
            ->groupBy('o.shop_id', 's.shop_name')
            ->orderByDesc('on_time_count')
            ->paginate(20);

        // 计算每个店铺的及时率
        $report->getCollection()->transform(function ($item) {
            $item->on_time_rate = $item->shipped_count > 0
                ? round($item->on_time_count / $item->shipped_count * 100, 2)
                : 0;
            $item->avg_ship_hours = round($item->avg_ship_hours ?? 0, 2);
            return $item;
        });

        // 汇总统计
        $totalShipped = $report->sum('shipped_count');
        $totalOnTime = $report->sum('on_time_count');
        $stats = [
            'total_shipped'  => $totalShipped,
            'total_on_time'  => $totalOnTime,
            'overall_rate'   => $totalShipped > 0
                ? round($totalOnTime / $totalShipped * 100, 2)
                : 0,
            'avg_ship_hours' => $report->count() > 0
                ? round($report->avg('avg_ship_hours'), 2)
                : 0,
        ];

        return view('report.operation.ship-on-time', compact('report', 'stats', 'shops', 'startDate', 'endDate'));
    }
}
