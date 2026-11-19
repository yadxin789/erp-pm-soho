<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 调价历史与采购报表
 *
 * 数据源：
 * - soho_price_change_requests / soho_price_change_tasks：调价申请与执行任务
 * - soho_procurement_orders / requests / plans / arrivals / approvals / soho_suppliers：采购链路
 */
class PriceReportController extends Controller
{
    /** 调价历史 */
    public function priceChangeHistory(Request $request)
    {
        $base = DB::table('soho_price_change_requests')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $rows = (clone $base)
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('direction'), function ($q, $dir) {
                $dir === 'up' ? $q->where('change_amount', '>', 0) : $q->where('change_amount', '<', 0);
            })
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('sku_code', 'like', "%{$kw}%")->orWhere('requested_by_name', 'like', "%{$kw}%");
            }))
            ->orderByDesc('requested_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'up' => (clone $base)->where('change_amount', '>', 0)->count(),
            'down' => (clone $base)->where('change_amount', '<', 0)->count(),
        ];

        // 执行任务：已申请调价后落地的执行记录
        $tasks = DB::table('soho_price_change_tasks')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'request_id', 'target_code', 'original_price', 'target_price', 'status', 'scheduled_at', 'executed_at', 'result_message']);

        $avgChange = (float) (clone $base)->where('status', 'approved')->avg('change_percentage');

        return view('soho.price.change-history', compact('rows', 'stats', 'tasks', 'avgChange'));
    }

    /** 采购报表 */
    public function procurementReport(Request $request)
    {
        $tenant = $this->tenantId();

        $orders = DB::table('soho_procurement_orders')
            ->when($tenant, fn ($q, $t) => $q->where('soho_procurement_orders.tenant_id', $t));
        $requests = DB::table('soho_procurement_requests')
            ->when($tenant, fn ($q, $t) => $q->where('tenant_id', $t));
        $plans = DB::table('soho_procurement_plans')
            ->when($tenant, fn ($q, $t) => $q->where('tenant_id', $t));

        $stats = [
            'order_count' => (clone $orders)->count(),
            'order_amount' => round((float) (clone $orders)->sum('total_amount'), 2),
            'pending_requests' => (clone $requests)->whereIn('status', ['pending', 'draft'])->count(),
            'plan_budget' => round((float) (clone $plans)->sum('budget'), 2),
            'arrivals' => DB::table('soho_procurement_arrivals')
                ->when($tenant, fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'approvals_pending' => DB::table('soho_procurement_approvals')
                ->when($tenant, fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'pending')->count(),
        ];

        // 按供应商汇总
        $bySupplier = (clone $orders)
            ->leftJoin('soho_suppliers as s', 's.id', '=', 'soho_procurement_orders.supplier_id')
            ->select('soho_procurement_orders.supplier_id', 's.name as supplier_name', 's.level')
            ->selectRaw('COUNT(soho_procurement_orders.id) as order_count')
            ->selectRaw('SUM(soho_procurement_orders.total_amount) as amount')
            ->groupBy('soho_procurement_orders.supplier_id', 's.name', 's.level')
            ->orderByDesc('amount')
            ->get();

        // 按订单状态
        $byStatus = (clone $orders)
            ->select('status')
            ->selectRaw('COUNT(*) as cnt')
            ->selectRaw('SUM(total_amount) as amount')
            ->groupBy('status')
            ->get();

        // 按月趋势
        $monthly = (clone $orders)
            ->selectRaw("DATE_FORMAT(COALESCE(ordered_at, created_at), '%Y-%m') as ym")
            ->selectRaw('COUNT(*) as cnt')
            ->selectRaw('SUM(total_amount) as amount')
            ->groupBy('ym')
            ->orderByDesc('ym')
            ->limit(12)
            ->get();

        // 需求单状态
        $requestStatus = (clone $requests)
            ->select('status')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('status')
            ->orderByDesc('cnt')
            ->get();

        return view('soho.report.procurement', compact('stats', 'bySupplier', 'byStatus', 'monthly', 'requestStatus'));
    }

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }
}
