<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 订单数据分析控制器
 * 展示订单趋势、转化率、客单价等核心数据分析指标
 */
class OrderReportController extends Controller
{
    /**
     * 订单数据分析首页
     * 按日期维度统计订单数据，展示趋势与汇总指标
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 店铺筛选
        $shopId = $request->input('shop_id');

        // 平台筛选
        $platform = $request->input('platform');

        // 平台选项
        $platformOptions = [
            'taobao' => '淘宝',
            'jd'     => '京东',
            'pdd'    => '拼多多',
            'douyin' => '抖音',
            'amazon' => '亚马逊',
        ];

        // 获取当前租户下的店铺列表
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 基础查询条件
        $baseCondition = function ($q) use ($tenantId, $shopId, $platform, $startDate, $endDate) {
            $q->where('tenant_id', $tenantId)
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->when($platform, fn($q) => $q->where('platform', $platform))
                ->whereBetween('order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        };

        // 按日期分组统计订单趋势
        $trend = DB::table('soho_platform_orders')
            ->where($baseCondition)
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(pay_amount) as sales_amount'),
                // 转化率指标：已支付订单数
                DB::raw('SUM(CASE WHEN pay_status = \'paid\' THEN 1 ELSE 0 END) as paid_count'),
                // 已完成订单数
                DB::raw('SUM(CASE WHEN order_status = \'completed\' THEN 1 ELSE 0 END) as completed_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 汇总统计
        $totalOrders = $trend->sum('order_count');
        $totalSales = $trend->sum('sales_amount');
        $totalPaid = $trend->sum('paid_count');
        $totalCompleted = $trend->sum('completed_count');

        $stats = [
            'total_orders'    => $totalOrders,
            'total_sales'     => $totalSales,
            // 客单价 = 销售额 / 订单数
            'avg_order_value' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
            // 支付转化率 = 已支付订单数 / 总订单数
            'conversion_rate' => $totalOrders > 0 ? round($totalPaid / $totalOrders * 100, 2) : 0,
            // 完成率 = 已完成订单数 / 总订单数
            'completion_rate' => $totalOrders > 0 ? round($totalCompleted / $totalOrders * 100, 2) : 0,
            // 日均订单数
            'daily_avg'       => $trend->count() > 0 ? round($totalOrders / $trend->count(), 2) : 0,
        ];

        // 按平台分组的销售统计
        $platformStats = DB::table('soho_platform_orders')
            ->where($baseCondition)
            ->select('platform', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(pay_amount) as sales_amount'))
            ->groupBy('platform')
            ->orderByDesc('sales_amount')
            ->get();

        return view('report.data.order', compact('trend', 'stats', 'platformStats', 'shops', 'platformOptions', 'startDate', 'endDate'));
    }
}
