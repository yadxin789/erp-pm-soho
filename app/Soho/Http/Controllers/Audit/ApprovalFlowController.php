<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 审批流程审计控制器
 * 负责审计审批流程的执行记录，支持按审批类型、结果、申请人筛选
 */
class ApprovalFlowController extends Controller
{
    /**
     * 审批流程审计列表
     * 展示当前租户下的审批流程记录，支持按审批单号、类型、结果筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询审批流程审计记录，限定当前租户
        $query = DB::table('soho_audit_approval_flows')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按审批单号、申请人、审批人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('approval_no', 'like', "%{$search}%")
                  ->orWhere('applicant', 'like', "%{$search}%")
                  ->orWhere('approver', 'like', "%{$search}%");
            });
        }
        // 按审批类型筛选
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        // 按审批结果筛选（approved/rejected）
        if ($result = $request->input('result')) {
            $query->where('result', $result);
        }
        // 按审批时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('approved_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按审批时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('approved_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按审批时间倒序分页，每页20条
        $approvalFlows = $query->orderByDesc('approved_at')->paginate(20);

        // 统计数据：基于当前租户的审批记录总数及各结果数量
        $baseQuery = DB::table('soho_audit_approval_flows')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'approved' => (clone $baseQuery)->where('result', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('result', 'rejected')->count(),
            'today'    => (clone $baseQuery)->whereDate('approved_at', today())->count(),
        ];

        // 审批结果筛选选项
        $resultOptions = [
            'approved' => '已通过',
            'rejected' => '已拒绝',
        ];

        // 审批类型筛选选项（常见审批业务场景）
        $typeOptions = [
            'product_publish'  => '商品发布',
            'price_change'     => '价格调整',
            'order_cancel'     => '订单取消',
            'inventory_adjust' => '库存调整',
            'refund'           => '退款审批',
            'purchase'         => '采购审批',
        ];

        return view('audit.approval-flows.index', compact('approvalFlows', 'stats', 'resultOptions', 'typeOptions'));
    }
}
