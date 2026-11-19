<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 财务看板（退款流水 / 平台结算 / 结算明细 / 费用分摊 / 对账任务 / 结算稽核 / 费用稽核 / 差异审批）
 *
 * 数据源：
 * - soho_refund_flows / soho_refund_records：退款流水与退款单
 * - soho_platform_settlements：平台结算单
 * - soho_finance_settles：财务结算单（按店铺）
 * - soho_finance_reconciles：对账任务
 * - soho_expense_allocations：费用分摊
 */
class FinanceBoardController extends Controller
{
    /** 退款流水 */
    public function refundFlow(Request $request)
    {
        $rows = DB::table('soho_refund_flows')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('refund_no', 'like', "%{$kw}%")
                    ->orWhere('order_no', 'like', "%{$kw}%")
                    ->orWhere('customer_name', 'like', "%{$kw}%");
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_refund_flows')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'amount' => (float) (clone $base)->sum('amount'),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'refunded' => (clone $base)->where('status', 'refunded')->count(),
        ];

        $records = DB::table('soho_refund_records')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'refund_no', 'refund_type', 'refund_amount', 'refund_status', 'failed_reason', 'created_at']);

        return view('soho.finance.refund-flow', compact('rows', 'stats', 'records'));
    }

    /** 平台结算 */
    public function platformSettlement(Request $request)
    {
        $rows = DB::table('soho_platform_settlements')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('settlement_no', 'like', "%{$kw}%"))
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_platform_settlements')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'amount' => (float) (clone $base)->sum('amount'),
            'settled' => (clone $base)->where('status', 'settled')->count(),
            'disputed' => (clone $base)->whereIn('status', ['disputed', 'rejected'])->count(),
        ];

        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');
        $shops = $this->shops();

        return view('soho.finance.platform-settlement', compact('rows', 'stats', 'platforms', 'shops'));
    }

    /** 结算明细（按店铺汇总财务结算单） */
    public function settlementDetail(Request $request)
    {
        $rows = DB::table('soho_finance_settles')
            ->when($request->input('shop_id'), fn ($q, $id) => $q->where('shop_id', $id))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('settle_no', 'like', "%{$kw}%"))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $byShop = DB::table('soho_finance_settles')
            ->select('shop_id')
            ->selectRaw('COUNT(*) as settle_count')
            ->selectRaw('SUM(amount) as amount_total')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount")
            ->groupBy('shop_id')
            ->get();

        $stats = [
            'total' => DB::table('soho_finance_settles')->count(),
            'amount' => (float) DB::table('soho_finance_settles')->sum('amount'),
            'pending' => DB::table('soho_finance_settles')->whereIn('status', ['pending', 'processing'])->count(),
            'completed' => DB::table('soho_finance_settles')->where('status', 'completed')->count(),
        ];

        $shops = $this->shops();

        return view('soho.finance.settlement-detail', compact('rows', 'stats', 'byShop', 'shops'));
    }

    /** 费用分摊 */
    public function costAllocation(Request $request)
    {
        $rows = DB::table('soho_expense_allocations')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('expense_type'), fn ($q, $type) => $q->where('expense_type', $type))
            ->when($request->input('allocation_method'), fn ($q, $m) => $q->where('allocation_method', $m))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_expense_allocations')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'amount' => (float) (clone $base)->sum('amount'),
            'allocated' => (float) (clone $base)->sum('allocated_amount'),
            'methods' => (clone $base)->select('allocation_method')->distinct()->count(),
        ];

        $types = (clone $base)->select('expense_type')->distinct()->pluck('expense_type');
        $methods = (clone $base)->select('allocation_method')->distinct()->pluck('allocation_method');

        return view('soho.finance.cost-allocation', compact('rows', 'stats', 'types', 'methods'));
    }

    /** 对账任务 */
    public function reconcileTask(Request $request)
    {
        $rows = DB::table('soho_finance_reconciles')
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('reconcile_no', 'like', "%{$kw}%"))
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => DB::table('soho_finance_reconciles')->count(),
            'pending' => DB::table('soho_finance_reconciles')->where('status', 'pending')->count(),
            'confirmed' => DB::table('soho_finance_reconciles')->where('status', 'confirmed')->count(),
            'amount' => (float) DB::table('soho_finance_reconciles')->sum('amount'),
        ];

        $shops = $this->shops();

        return view('soho.finance.reconcile-task', compact('rows', 'stats', 'shops'));
    }

    /** 结算稽核（待审 / 已审结算单） */
    public function settlementAudit(Request $request)
    {
        $rows = DB::table('soho_finance_settles')
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('settle_no', 'like', "%{$kw}%"))
            ->orderByRaw("FIELD(status,'pending','processing','completed','failed','cancelled')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_finance_settles');
        $stats = [
            'total' => (clone $base)->count(),
            'waiting' => (clone $base)->whereIn('status', ['pending', 'processing'])->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];

        $shops = $this->shops();

        return view('soho.finance.settlement-audit', compact('rows', 'stats', 'shops'));
    }

    /** 费用稽核（分摊金额与费用金额偏差） */
    public function costAudit(Request $request)
    {
        $rows = DB::table('soho_expense_allocations')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('expense_type'), fn ($q, $type) => $q->where('expense_type', $type))
            ->when($request->input('only_diff') === '1', fn ($q) => $q->whereRaw('ABS(allocated_amount - amount) > 0.01'))
            ->orderByRaw('ABS(allocated_amount - amount) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_expense_allocations')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'diff' => (clone $base)->whereRaw('ABS(allocated_amount - amount) > 0.01')->count(),
            'amount' => (float) (clone $base)->sum('amount'),
            'allocated' => (float) (clone $base)->sum('allocated_amount'),
        ];

        $byType = (clone $base)
            ->select('expense_type')
            ->selectRaw('COUNT(*) as cnt')
            ->selectRaw('SUM(amount) as amount_total')
            ->selectRaw('SUM(allocated_amount) as allocated_total')
            ->groupBy('expense_type')
            ->get();

        $types = (clone $base)->select('expense_type')->distinct()->pluck('expense_type');

        return view('soho.finance.cost-audit', compact('rows', 'stats', 'byType', 'types'));
    }

    /** 平台结算差异审批 */
    public function diffApproval(Request $request)
    {
        $rows = DB::table('soho_platform_settlements')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereIn('status', ['disputed', 'pending', 'rejected', 'settled'])
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // 财务侧按店铺汇总，用于与平台结算金额比对
        $financeByShop = DB::table('soho_finance_settles')
            ->select('shop_id')
            ->selectRaw('SUM(amount) as finance_amount')
            ->groupBy('shop_id')
            ->pluck('finance_amount', 'shop_id');

        $platformByShop = DB::table('soho_platform_settlements')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->select('shop_id')
            ->selectRaw('SUM(amount) as platform_amount')
            ->groupBy('shop_id')
            ->get()
            ->map(function ($row) use ($financeByShop) {
                $finance = (float) ($financeByShop[$row->shop_id] ?? 0);
                $row->finance_amount = $finance;
                $row->diff = round((float) $row->platform_amount - $finance, 2);
                return $row;
            })
            ->sortByDesc(fn ($row) => abs($row->diff))
            ->values();

        $stats = [
            'shops' => $platformByShop->count(),
            'diff_shops' => $platformByShop->filter(fn ($r) => abs($r->diff) > 0.01)->count(),
            'diff_amount' => $platformByShop->sum(fn ($r) => abs($r->diff)),
            'disputed' => DB::table('soho_platform_settlements')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('status', 'disputed')->count(),
        ];

        $shops = $this->shops();

        return view('soho.finance.diff-approval', compact('rows', 'platformByShop', 'stats', 'shops'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }

    private function shops()
    {
        return DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');
    }
}
