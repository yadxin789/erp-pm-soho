<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购申请控制器
 * 用于管理采购申请的列表展示与筛选
 */
class ProcurementRequestController extends Controller
{
    /**
     * 采购申请列表
     * 支持按状态、申请人筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $requests = DB::table('soho_procurement_requests')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($applicant = $request->input('applicant'), function ($query) use ($applicant) {
                $query->where('applicant_id', $applicant);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('request_no', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_requests')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_procurement_requests')->where('status', 'pending')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'approved' => DB::table('soho_procurement_requests')->where('status', 'approved')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'rejected' => DB::table('soho_procurement_requests')->where('status', 'rejected')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $statusOptions = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已审核',
            'rejected' => '已驳回',
            'ordered' => '已下单',
            'closed' => '已关闭',
        ];

        return view('procurement.requests.index', compact('requests', 'statusOptions', 'stats'));
    }
}