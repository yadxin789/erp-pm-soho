<?php

namespace App\Soho\Http\Controllers\Approval;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 调价审批控制器（统一审批中心入口）
 * 作为统一审批中心的调价审批入口，聚合价格中心提交的调价申请
 * 注意：价格中心已有独立的 Price\PriceChangeApprovalController，本控制器仅作为审批中心统一入口
 */
class PriceChangeApprovalController extends Controller
{
    /**
     * 调价审批列表
     * 展示当前租户下的调价审批申请，支持按商品、申请人、状态等筛选并返回统计数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询调价审批申请，限定当前租户
        $query = DB::table('soho_price_change_requests')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按商品名称、SKU、申请人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('sku_code', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%");
            });
        }
        // 按状态筛选：pending/approved/rejected
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按申请时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('requested_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按申请时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('requested_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按申请时间倒序分页，每页20条
        $approvals = $query->orderByDesc('requested_at')->paginate(20);

        // 保留查询参数到分页链接
        $approvals->appends($request->query());

        // 统计数据：基于当前租户的调价审批总数及各状态数量
        $baseQuery = DB::table('soho_price_change_requests')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'pending'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // 状态筛选选项
        $statusOptions = ['pending' => '待审批', 'approved' => '已批准', 'rejected' => '已驳回'];

        return view('approval.price-change.index', compact('approvals', 'stats', 'statusOptions', 'search'));
    }
}
