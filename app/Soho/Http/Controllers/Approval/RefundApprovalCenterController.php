<?php

namespace App\Soho\Http\Controllers\Approval;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 退款审批中心控制器（统一入口）
 * 作为统一审批中心的退款审批入口，聚合售后模块提交的退款申请
 * 注意：售后模块已有独立的 Aftersale\RefundApprovalController，本控制器仅作为审批中心统一入口
 */
class RefundApprovalCenterController extends Controller
{
    /**
     * 退款审批列表
     * 展示当前租户下的退款审批申请，支持按售后单号、订单号、客户、状态等筛选并返回统计数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询退款审批单（type=refund），限定当前租户
        $query = DB::table('soho_order_after_sales')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('type', 'refund');

        // 关键词搜索：支持按售后单号、订单号、客户名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('after_sale_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        // 按状态筛选：pending/approved/rejected
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

        // 保留查询参数到分页链接
        $refunds->appends($request->query());

        // 统计数据：基于当前租户的退款审批总数及各状态数量
        $baseQuery = DB::table('soho_order_after_sales')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('type', 'refund');
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'pending'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // 状态筛选选项
        $statusOptions = ['pending' => '待审批', 'approved' => '已批准', 'rejected' => '已驳回'];

        // 退款总金额：统计当前租户下所有退款申请的金额合计
        $totalRefundAmount = (clone $baseQuery)->sum('refund_amount');

        return view('approval.refund.index', compact('refunds', 'stats', 'statusOptions', 'search', 'totalRefundAmount'));
    }
}
