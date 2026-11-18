<?php

namespace App\Soho\Http\Controllers\Approval;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 大额差异审批控制器
 * 负责处理大额财务差异申请的审批流程，统一审批中心入口
 * 支持按单号、类型、申请人、状态等维度筛选
 */
class FinanceDiffApprovalController extends Controller
{
    /**
     * 大额差异审批列表
     * 展示当前租户下的大额差异审批申请，支持多条件筛选并返回统计数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询大额差异审批单，限定当前租户
        $query = DB::table('soho_finance_diff_approvals')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按单号、申请人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('doc_no', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%");
            });
        }
        // 按类型筛选：如订单差异、退款差异、结算差异等
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        // 按状态筛选：pending/approved/rejected
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按申请时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按申请时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按申请时间倒序分页，每页20条
        $approvals = $query->orderByDesc('created_at')->paginate(20);

        // 保留查询参数到分页链接
        $approvals->appends($request->query());

        // 统计数据：基于当前租户的大额差异审批总数及各状态数量
        $baseQuery = DB::table('soho_finance_diff_approvals')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'         => (clone $baseQuery)->count(),
            'pending'       => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved'      => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected'      => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // 状态筛选选项
        $statusOptions = ['pending' => '待审批', 'approved' => '已批准', 'rejected' => '已驳回'];

        // 类型筛选选项：常见的财务差异类型
        $typeOptions = [
            'order'      => '订单差异',
            'refund'     => '退款差异',
            'settlement' => '结算差异',
            'payment'    => '付款差异',
            'other'      => '其他差异',
        ];

        // 差异金额合计：统计当前租户下所有大额差异的金额合计
        $totalDiffAmount = (clone $baseQuery)->sum('diff_amount');

        return view('approval.finance-diff.index', compact('approvals', 'stats', 'statusOptions', 'typeOptions', 'search', 'totalDiffAmount'));
    }
}
