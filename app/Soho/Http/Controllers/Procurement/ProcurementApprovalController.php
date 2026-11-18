<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购审批控制器
 * 用于管理采购审批流程的列表展示与处理
 */
class ProcurementApprovalController extends Controller
{
    /**
     * 采购审批列表
     * 支持按审批类型、状态、时间筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $approvals = DB::table('soho_procurement_approvals')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($type = $request->input('type'), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('submitted_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('submitted_at', '<=', $dateTo . ' 23:59:59');
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('approval_no', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('submitted_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_approvals')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_procurement_approvals')->where('status', 'pending')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'approved' => DB::table('soho_procurement_approvals')->where('status', 'approved')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'rejected' => DB::table('soho_procurement_approvals')->where('status', 'rejected')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $typeOptions = [
            'plan' => '采购计划',
            'request' => '采购申请',
            'order' => '采购订单',
            'budget' => '采购预算',
        ];

        $statusOptions = [
            'pending' => '待审批',
            'approved' => '已通过',
            'rejected' => '已驳回',
            'returned' => '已退回',
        ];

        return view('procurement.approvals.index', compact('approvals', 'typeOptions', 'statusOptions', 'stats'));
    }
}