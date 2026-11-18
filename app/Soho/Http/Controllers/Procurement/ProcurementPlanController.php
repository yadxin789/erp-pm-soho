<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购计划控制器
 * 用于管理采购计划的列表展示与筛选
 */
class ProcurementPlanController extends Controller
{
    /**
     * 采购计划列表
     * 支持按状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $plans = DB::table('soho_procurement_plans')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('planned_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('planned_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('planned_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_plans')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_procurement_plans')->where('status', 'pending')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'approved' => DB::table('soho_procurement_plans')->where('status', 'approved')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'completed' => DB::table('soho_procurement_plans')->where('status', 'completed')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $statusOptions = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已审核',
            'executing' => '执行中',
            'completed' => '已完成',
            'cancelled' => '已取消',
        ];

        return view('procurement.plans.index', compact('plans', 'statusOptions', 'stats'));
    }
}