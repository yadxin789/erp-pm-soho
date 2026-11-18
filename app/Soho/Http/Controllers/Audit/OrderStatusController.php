<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 订单状态审计控制器
 * 负责审计订单状态变更记录，支持按订单号、操作人、时间范围筛选
 */
class OrderStatusController extends Controller
{
    /**
     * 订单状态审计列表
     * 展示当前租户下的订单状态变更记录，支持按订单号、操作人、时间范围筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询订单状态变更审计记录，限定当前租户
        $query = DB::table('soho_audit_order_statuses')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按订单号、操作人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhere('operator', 'like', "%{$search}%");
            });
        }
        // 按旧状态筛选
        if ($oldStatus = $request->input('old_status')) {
            $query->where('old_status', $oldStatus);
        }
        // 按新状态筛选
        if ($newStatus = $request->input('new_status')) {
            $query->where('new_status', $newStatus);
        }
        // 按操作时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('operated_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按操作时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('operated_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按操作时间倒序分页，每页20条
        $orderStatuses = $query->orderByDesc('operated_at')->paginate(20);

        // 统计数据：基于当前租户的订单状态变更总数及今日、本周统计
        $baseQuery = DB::table('soho_audit_order_statuses')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'  => (clone $baseQuery)->count(),
            'today'  => (clone $baseQuery)->whereDate('operated_at', today())->count(),
            'week'   => (clone $baseQuery)->where('operated_at', '>=', now()->startOfWeek())->count(),
            'cancel' => (clone $baseQuery)->where('new_status', 'cancelled')->count(),
        ];

        // 订单状态筛选选项（常见订单状态流转）
        $statusOptions = [
            'pending'     => '待处理',
            'paid'        => '已付款',
            'shipped'     => '已发货',
            'completed'   => '已完成',
            'cancelled'   => '已取消',
            'refunded'    => '已退款',
            'on_hold'     => '已挂起',
        ];

        return view('audit.order-statuses.index', compact('orderStatuses', 'stats', 'statusOptions'));
    }
}
