<?php

namespace App\Soho\Http\Controllers\Logistics;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 物流时效统计控制器
 * 用于展示发货及时率、平均物流时效、超时订单数等统计数据
 */
class LogisticsMetricsController extends Controller
{
    /**
     * 物流时效统计
     * 支持按时间范围筛选（近7天/30天/90天），返回时效分布数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选，默认近30天
        $range = $request->input('range', '30');
        $ranges = ['7' => '近7天', '30' => '近30天', '90' => '近90天'];
        $days = in_array($range, ['7', '30', '90']) ? (int) $range : 30;
        $dateFrom = now()->subDays($days)->startOfDay()->toDateTimeString();
        $dateTo = now()->endOfDay()->toDateTimeString();

        // 基础查询条件（租户隔离 + 时间范围 + 已发货包裹）
        $baseQuery = function () use ($tenantId, $dateFrom, $dateTo) {
            return DB::table('soho_order_packages')
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('waybill_no')
                ->whereBetween('shipped_at', [$dateFrom, $dateTo]);
        };

        // 总发货包裹数
        $totalShipped = $baseQuery()->count();
        // 24小时内发货的包裹数（及时发货）
        $onTimeShipped = $baseQuery()
            ->whereRaw('TIMESTAMPDIFF(HOUR, order_created_at, shipped_at) <= 24')
            ->count();
        // 超过24小时发货的包裹数（超时发货）
        $overtimeShipped = $baseQuery()
            ->whereRaw('TIMESTAMPDIFF(HOUR, order_created_at, shipped_at) > 24')
            ->count();
        // 已签收包裹数
        $deliveredCount = $baseQuery()->whereNotNull('delivered_at')->count();
        // 超时未签收包裹数（发货后超过7天未签收）
        $overtimeUndelivered = $baseQuery()
            ->whereNull('delivered_at')
            ->whereRaw('TIMESTAMPDIFF(HOUR, shipped_at, NOW()) > 168')
            ->count();

        // 计算发货及时率（百分比，保留两位小数）
        $onTimeRate = $totalShipped > 0 ? round(($onTimeShipped / $totalShipped) * 100, 2) : 0;
        // 计算平均物流时效（小时，已签收包裹从发货到签收的平均时长）
        $avgTransitHours = 0;
        if ($deliveredCount > 0) {
            $avgTransitSeconds = $baseQuery()
                ->whereNotNull('delivered_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, shipped_at, delivered_at)) as avg_seconds')
                ->value('avg_seconds');
            $avgTransitHours = $avgTransitSeconds ? round($avgTransitSeconds / 3600, 2) : 0;
        }

        // 时效分布统计（按发货到签收时长区间分组）
        $durationDistribution = $baseQuery()
            ->whereNotNull('delivered_at')
            ->selectRaw("
                CASE
                    WHEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) <= 24 THEN 'within_24h'
                    WHEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) <= 48 THEN 'within_48h'
                    WHEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) <= 72 THEN 'within_72h'
                    WHEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) <= 120 THEN 'within_120h'
                    ELSE 'over_120h'
                END as duration_range,
                COUNT(*) as count
            ")
            ->groupBy('duration_range')
            ->pluck('count', 'duration_range')
            ->toArray();

        // 时效分布展示数据（保证所有区间都有值）
        $distribution = [
            'within_24h' => $durationDistribution['within_24h'] ?? 0,
            'within_48h' => $durationDistribution['within_48h'] ?? 0,
            'within_72h' => $durationDistribution['within_72h'] ?? 0,
            'within_120h' => $durationDistribution['within_120h'] ?? 0,
            'over_120h' => $durationDistribution['over_120h'] ?? 0,
        ];

        // 时效分布标签
        $distributionLabels = [
            'within_24h' => '24小时内',
            'within_48h' => '24-48小时',
            'within_72h' => '48-72小时',
            'within_120h' => '72-120小时',
            'over_120h' => '120小时以上',
        ];

        // 按快递公司统计平均时效
        $carrierMetrics = $baseQuery()
            ->whereNotNull('delivered_at')
            ->select(
                'express_company',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) <= 72 THEN 1 ELSE 0 END) as on_time_count'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(HOUR, shipped_at, delivered_at)), 2) as avg_hours')
            )
            ->groupBy('express_company')
            ->get()
            ->map(function ($item) {
                // 计算各快递公司72小时内签收率
                $item->on_time_rate = $item->total > 0 ? round(($item->on_time_count / $item->total) * 100, 2) : 0;
                return $item;
            });

        // 统计汇总数据
        $stats = [
            // 总发货包裹数
            'total_shipped' => $totalShipped,
            // 发货及时率（%）
            'on_time_rate' => $onTimeRate,
            // 平均物流时效（小时）
            'avg_transit_hours' => $avgTransitHours,
            // 超时发货订单数
            'overtime_shipped' => $overtimeShipped,
            // 超时未签收订单数
            'overtime_undelivered' => $overtimeUndelivered,
            // 已签收包裹数
            'delivered_count' => $deliveredCount,
        ];

        return view('logistics.metrics.index', compact(
            'stats',
            'ranges',
            'range',
            'distribution',
            'distributionLabels',
            'carrierMetrics'
        ));
    }
}
