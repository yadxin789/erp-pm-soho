<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 退款流水控制器
 * 用于查询退款流水记录，支持按退款单号、订单号、客户、状态筛选
 */
class RefundFlowController extends Controller
{
    /**
     * 退款流水列表
     * 支持按退款单号、订单号、客户、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建退款流水查询，限定当前租户
        $query = DB::table('soho_refund_flows')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按退款单号、订单号、客户名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('refund_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        // 按退款状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按创建时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按创建时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按创建时间倒序分页，每页20条
        $refunds = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的退款流水总数及各状态数量、总金额
        $baseQuery = DB::table('soho_refund_flows')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            // 退款总数
            'total'      => (clone $baseQuery)->count(),
            // 待审核
            'pending'    => (clone $baseQuery)->where('status', 'pending')->count(),
            // 已退款
            'refunded'   => (clone $baseQuery)->where('status', 'refunded')->count(),
            // 已拒绝
            'rejected'   => (clone $baseQuery)->where('status', 'rejected')->count(),
            // 退款总金额
            'total_amount' => (clone $baseQuery)->sum('amount'),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'  => '待审核',
            'approved' => '审核通过',
            'refunded' => '已退款',
            'rejected' => '已拒绝',
        ];

        return view('finance.refund-flow.index', compact('refunds', 'stats', 'statusOptions'));
    }
}
