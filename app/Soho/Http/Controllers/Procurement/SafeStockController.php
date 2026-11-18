<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 安全库存控制器
 * 用于管理安全库存的列表展示与监控
 */
class SafeStockController extends Controller
{
    /**
     * 安全库存列表
     * 支持按仓库、商品、预警状态筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $safeStocks = DB::table('soho_safe_stocks')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($warehouseId = $request->input('warehouse_id'), function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($alertLevel = $request->input('alert_level'), function ($query) use ($alertLevel) {
                $query->where('alert_level', $alertLevel);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sku_code', 'like', "%{$search}%")
                      ->orWhere('product_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20);

        // 获取仓库选项
        $warehouseOptions = DB::table('soho_warehouses')->pluck('name', 'id')->toArray();

        // 统计数据
        $stats = [
            'total' => DB::table('soho_safe_stocks')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'below_safe' => DB::table('soho_safe_stocks')->where('alert_level', 'low')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'near_safe' => DB::table('soho_safe_stocks')->where('alert_level', 'warn')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'normal' => DB::table('soho_safe_stocks')->where('alert_level', 'normal')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $alertLevelOptions = [
            'normal' => '正常',
            'warning' => '预警',
            'danger' => '告警',
            'out' => '缺货',
        ];

        return view('procurement.safe-stocks.index', compact('safeStocks', 'alertLevelOptions', 'warehouseOptions', 'stats'));
    }
}