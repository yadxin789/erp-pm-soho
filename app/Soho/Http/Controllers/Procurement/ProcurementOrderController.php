<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 采购订单控制器
 * 用于管理采购订单的列表展示与筛选
 */
class ProcurementOrderController extends Controller
{
    /**
     * 采购订单列表
     * 支持按状态、供应商、时间筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $orders = DB::table('soho_procurement_orders')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($supplierId = $request->input('supplier_id'), function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('ordered_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('ordered_at', '<=', $dateTo . ' 23:59:59');
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                      ->orWhere('remark', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('ordered_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_procurement_orders')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'shipping' => DB::table('soho_procurement_orders')->whereIn('status', ['confirmed', 'shipped'])->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'completed' => DB::table('soho_procurement_orders')->where('status', 'completed')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'cancelled' => DB::table('soho_procurement_orders')->where('status', 'cancelled')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $statusOptions = [
            'pending' => '待确认',
            'confirmed' => '已确认',
            'shipped' => '已发货',
            'arrived' => '已到货',
            'completed' => '已完成',
            'cancelled' => '已取消',
        ];

        return view('procurement.orders.index', compact('orders', 'statusOptions', 'stats'));
    }
}