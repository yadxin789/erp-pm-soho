<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购到货控制器
 * 用于管理采购到货的列表展示与筛选
 */
class ProcurementArrivalController extends Controller
{
    /**
     * 采购到货列表
     * 支持按状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $arrivals = DB::table('soho_procurement_arrivals')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('arrived_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('arrived_at', '<=', $dateTo . ' 23:59:59');
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('arrival_no', 'like', "%{$search}%")
                      ->orWhere('order_no', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('arrived_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_arrivals')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_procurement_arrivals')->where('status', 'pending')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'inspecting' => DB::table('soho_procurement_arrivals')->where('status', 'inspecting')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'completed' => DB::table('soho_procurement_arrivals')->where('status', 'completed')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $statusOptions = [
            'pending' => '待验收',
            'accepted' => '已验收',
            'rejected' => '已拒收',
            'partial' => '部分验收',
            'inspecting' => '验收中',
        ];

        return view('procurement.arrivals.index', compact('arrivals', 'statusOptions', 'stats'));
    }
}