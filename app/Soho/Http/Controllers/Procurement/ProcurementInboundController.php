<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购入库协同控制器
 * 用于管理采购入库的协同处理与列表展示
 */
class ProcurementInboundController extends Controller
{
    /**
     * 采购入库协同列表
     * 支持按状态、仓库、时间筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $inbounds = DB::table('soho_procurement_inbounds')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($warehouseId = $request->input('warehouse_id'), function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('inbound_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('inbound_at', '<=', $dateTo . ' 23:59:59');
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('inbound_no', 'like', "%{$search}%")
                      ->orWhere('arrival_no', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('inbound_at')
            ->paginate(20);

        // 获取仓库选项
        $warehouseOptions = DB::table('soho_warehouses')->pluck('name', 'id')->toArray();

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_inbounds')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_procurement_inbounds')->where('status', 'pending')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'processing' => DB::table('soho_procurement_inbounds')->where('status', 'processing')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'completed' => DB::table('soho_procurement_inbounds')->where('status', 'completed')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $statusOptions = [
            'pending' => '待入库',
            'processing' => '入库中',
            'completed' => '已入库',
            'cancelled' => '已取消',
        ];

        return view('procurement.inbounds.index', compact('inbounds', 'statusOptions', 'warehouseOptions', 'stats'));
    }
}