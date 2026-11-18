<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 运营仪表盘控制器
 * 展示核心运营指标：今日订单数、待发货数、待退款数、异常订单数，支持按店铺筛选
 */
class OperationDashboardController extends Controller
{
    /**
     * 运营仪表盘首页
     * 汇总当前租户的核心运营指标，支持按店铺筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 店铺筛选参数
        $shopId = $request->input('shop_id');

        // 获取当前租户下的店铺列表（用于筛选下拉框）
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 今日日期范围
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        // 今日订单数：当日新建订单总数
        $todayOrders = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->whereBetween('order_created_at', [$todayStart, $todayEnd])
            ->count();

        // 待发货数：状态为待发货的订单数
        $pendingShip = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('order_status', 'pending')
            ->count();

        // 待退款数：存在待处理售后单（退款类型）的订单数
        $pendingRefund = DB::table('soho_order_after_sales')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('type', 'refund')
            ->where('status', 'pending')
            ->count();

        // 异常订单数：标记为异常的订单数
        $abnormalOrders = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('is_abnormal', 1)
            ->count();

        // 统计卡片数据
        $stats = [
            'today_orders'    => $todayOrders,
            'pending_ship'    => $pendingShip,
            'pending_refund'  => $pendingRefund,
            'abnormal_orders' => $abnormalOrders,
        ];

        // 近7天订单趋势（用于图表展示）
        $trend = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('order_created_at', '>=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(pay_amount) as sales_amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 待办事项列表（最近的异常订单）
        $recentAbnormal = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('is_abnormal', 1)
            ->orderByDesc('order_created_at')
            ->limit(10)
            ->get();

        return view('report.operation.dashboard', compact('stats', 'shops', 'trend', 'recentAbnormal'));
    }
}
