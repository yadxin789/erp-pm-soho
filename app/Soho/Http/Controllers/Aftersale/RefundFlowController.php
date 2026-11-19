<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 退款执行闭环（P1-2）
 *
 * 售后单（soho_order_after_sales） → 退款单（soho_refund_records）
 * 退款单状态机：pending（待审核） → processing（退款中） → completed（已退款）
 *              pending / processing → failed（驳回或退款失败，记录原因）
 *
 * 退款完成时（completed）：写 soho_refund_flows（approved）、回写售后单 completed、
 * 售后明细与订单明细退款金额、soho_operation_logs 留痕。
 */
class RefundFlowController extends Controller
{
    /** 退款单台账 */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $rows = DB::table('soho_refund_records as r')
            ->leftJoin('soho_order_after_sales as a', 'a.id', '=', 'r.after_sale_id')
            ->leftJoin('soho_platform_orders as o', 'o.id', '=', 'r.order_id')
            ->when($tenantId, fn ($q, $t) => $q->where('r.tenant_id', $t))
            ->when($request->input('status'), fn ($q, $s) => $q->where('r.refund_status', $s))
            ->when($request->input('type'), fn ($q, $t) => $q->where('r.refund_type', $t))
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('r.refund_no', 'like', "%{$search}%")
                        ->orWhere('a.after_sale_no', 'like', "%{$search}%")
                        ->orWhere('o.system_order_no', 'like', "%{$search}%")
                        ->orWhere('o.buyer_nick', 'like', "%{$search}%");
                });
            })
            ->select(
                'r.id', 'r.refund_no', 'r.refund_type', 'r.refund_amount', 'r.refund_status',
                'r.platform_refund_no', 'r.created_at',
                'a.after_sale_no', 'a.status as after_sale_status', 'a.type as after_sale_type',
                'o.system_order_no', 'o.buyer_nick', 'o.order_status'
            )
            ->orderByDesc('r.id')
            ->paginate(15)
            ->withQueryString();

        $base = fn () => DB::table('soho_refund_records')->when($tenantId, fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => $base()->count(),
            'pending' => $base()->where('refund_status', 'pending')->count(),
            'processing' => $base()->where('refund_status', 'processing')->count(),
            'completed' => $base()->where('refund_status', 'completed')->count(),
            'failed' => $base()->where('refund_status', 'failed')->count(),
            'amount' => (float) $base()->where('refund_status', 'completed')->sum('refund_amount'),
        ];

        // 可生成退款单的售后单（refund/return，已批准或待处理，且尚无退款单）
        $candidates = DB::table('soho_order_after_sales as a')
            ->leftJoin('soho_refund_records as r', 'r.after_sale_id', '=', 'a.id')
            ->when($tenantId, fn ($q, $t) => $q->where('a.tenant_id', $t))
            ->whereNull('r.id')
            ->whereIn('a.type', ['refund', 'return'])
            ->whereIn('a.status', ['pending', 'completed'])
            ->orderByDesc('a.id')
            ->limit(20)
            ->get(['a.id', 'a.after_sale_no', 'a.type', 'a.status', 'a.refund_amount', 'a.buyer_nick']);

        return view('soho.aftersale.refund-records', compact('rows', 'stats', 'candidates'));
    }

    /** 退款单详情 */
    public function show(int $id)
    {
        $r = $this->findOrFail($id);

        $afterSale = $r->after_sale_id ? DB::table('soho_order_after_sales')->where('id', $r->after_sale_id)->first() : null;
        $order = $r->order_id ? DB::table('soho_platform_orders')->where('id', $r->order_id)->first() : null;
        $items = $r->after_sale_id
            ? DB::table('soho_after_sale_items')->where('after_sale_id', $r->after_sale_id)->get()
            : collect();
        $flows = DB::table('soho_refund_flows')->where('refund_no', $r->refund_no)->orderByDesc('id')->get();
        $payments = $order ? DB::table('soho_order_payments')->where('order_id', $order->id)->get() : collect();

        return view('soho.aftersale.refund-flow', compact('r', 'afterSale', 'order', 'items', 'flows', 'payments'));
    }

    /** 由售后单生成退款单 */
    public function store(Request $request)
    {
        $data = $request->validate([
            'after_sale_id' => ['required', 'integer'],
            'refund_type' => ['nullable', 'in:refund,return_refund'],
        ], [], ['after_sale_id' => '售后单']);

        $tenantId = Auth::user()->tenant_id;
        $as = DB::table('soho_order_after_sales')
            ->when($tenantId, fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $data['after_sale_id'])
            ->first();

        if (! $as) {
            return back()->with('warning', '售后单不存在。');
        }
        if (DB::table('soho_refund_records')->where('after_sale_id', $as->id)->exists()) {
            return back()->with('warning', '该售后单已生成过退款单，请勿重复生成。');
        }

        DB::table('soho_refund_records')->insert([
            'tenant_id' => $tenantId,
            'after_sale_id' => $as->id,
            'order_id' => $as->platform_order_id,
            'refund_no' => 'RF'.now()->format('YmdHis').rand(10, 99),
            'refund_type' => $data['refund_type'] ?? ($as->type === 'return' ? 'return_refund' : 'refund'),
            'refund_amount' => (float) $as->refund_amount,
            'refund_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('soho.refund.records')->with('success', "已为售后单 {$as->after_sale_no} 生成退款单，待审核。");
    }

    /** 审核通过：pending → processing */
    public function approve(int $id)
    {
        $r = $this->findOrFail($id);
        if ($r->refund_status !== 'pending') {
            return back()->with('warning', '只有待审核的退款单可以审核通过。');
        }

        DB::table('soho_refund_records')->where('id', $id)->update([
            'refund_status' => 'processing',
            'updated_at' => now(),
        ]);

        return back()->with('success', '审核通过，退款单进入退款处理中。');
    }

    /** 驳回 / 标记失败 */
    public function reject(Request $request, int $id)
    {
        $data = $request->validate([
            'failed_reason' => ['required', 'string', 'max:200'],
        ], [], ['failed_reason' => '失败原因']);

        $r = $this->findOrFail($id);
        if (! in_array($r->refund_status, ['pending', 'processing'], true)) {
            return back()->with('warning', '当前状态不能驳回。');
        }

        DB::table('soho_refund_records')->where('id', $id)->update([
            'refund_status' => 'failed',
            'failed_reason' => $data['failed_reason'],
            'updated_at' => now(),
        ]);

        return back()->with('success', '退款单已标记失败：'.$data['failed_reason']);
    }

    /** 执行退款：processing → completed（写退款流水 + 回写售后/订单明细） */
    public function execute(int $id)
    {
        $r = $this->findOrFail($id);
        if ($r->refund_status !== 'processing') {
            return back()->with('warning', '只有退款处理中的单据可以执行退款。');
        }

        $order = $r->order_id ? DB::table('soho_platform_orders')->where('id', $r->order_id)->first() : null;
        if (! $order) {
            return back()->with('warning', '关联订单不存在，无法执行退款。');
        }

        $existingFlow = DB::table('soho_refund_flows')->where('refund_no', $r->refund_no)->exists();
        if ($existingFlow) {
            return back()->with('warning', '该退款单已有退款流水，请勿重复执行。');
        }

        DB::transaction(function () use ($r, $order) {
            $now = now();
            $platformNo = 'PR'.now()->format('YmdHis').rand(10, 99);

            DB::table('soho_refund_records')->where('id', $r->id)->update([
                'refund_status' => 'completed',
                'platform_refund_no' => $platformNo,
                'platform_response' => json_encode(['result' => 'success', 'channel' => 'manual', 'at' => $now->toDateTimeString()], JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            // 退款流水（财务口径）
            DB::table('soho_refund_flows')->insert([
                'tenant_id' => $r->tenant_id,
                'refund_no' => $r->refund_no,
                'order_no' => $order->system_order_no,
                'customer_name' => $order->buyer_nick,
                'status' => 'approved',
                'amount' => (float) $r->refund_amount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 售后单回写
            if ($r->after_sale_id) {
                DB::table('soho_order_after_sales')->where('id', $r->after_sale_id)->update([
                    'status' => 'completed',
                    'handle_time' => $now,
                    'handle_result' => '退款已完成：'.$platformNo,
                    'updated_at' => $now,
                ]);

                // 售后明细退款状态
                DB::table('soho_after_sale_items')->where('after_sale_id', $r->after_sale_id)
                    ->update(['refund_status' => 'completed', 'updated_at' => $now]);
            }

            // 订单明细累计退款金额（按售后明细的 SKU 分摊；无明细则按订单均摊顺序写入首行）
            $asItems = $r->after_sale_id
                ? DB::table('soho_after_sale_items')->where('after_sale_id', $r->after_sale_id)->get()
                : collect();

            if ($asItems->isEmpty()) {
                $firstItem = DB::table('soho_order_items')->where('order_id', $order->id)->orderBy('id')->first();
                if ($firstItem) {
                    DB::table('soho_order_items')->where('id', $firstItem->id)
                        ->update(['refund_amount' => DB::raw('refund_amount + '.(float) $r->refund_amount), 'updated_at' => $now]);
                }
            } else {
                // 逐行分摊：以售后明细 refund_amount 为准，缺失时按退款总额/行数均摊
                $rowAmounts = $asItems->map(fn ($i) => (float) $i->refund_amount)->filter(fn ($v) => $v > 0);
                $fallback = $asItems->count() > 0 ? (float) $r->refund_amount / $asItems->count() : 0;

                foreach ($asItems as $i) {
                    $amount = (float) $i->refund_amount > 0 ? (float) $i->refund_amount
                        : ($rowAmounts->isNotEmpty() ? 0 : $fallback);
                    if ($amount <= 0) {
                        continue;
                    }
                    $orderItem = $i->order_item_id
                        ? DB::table('soho_order_items')->where('id', $i->order_item_id)->first()
                        : DB::table('soho_order_items')->where('order_id', $order->id)->where('sku_id', $i->sku_id)->first();

                    if ($orderItem) {
                        DB::table('soho_order_items')->where('id', $orderItem->id)
                            ->update(['refund_amount' => DB::raw('refund_amount + '.$amount), 'updated_at' => $now]);
                    }
                }
            }

            // 留痕（P2-3：统一写 Core operation_logs，module=soho）
            DB::table('operation_logs')->insert([
                'module' => 'soho',
                'tenant_id' => $r->tenant_id,
                'user_id' => Auth::id(),
                'action' => 'execute',
                'target_type' => 'Refund',
                'target_id' => $r->id,
                'description' => '退款执行完成：'.$r->refund_no.' / '.$platformNo,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', "退款执行成功：{$r->refund_no}，退款流水已登记。");
    }

    // ---------------------------------------------------------------- 内部方法

    private function findOrFail(int $id): object
    {
        return DB::table('soho_refund_records')
            ->when(Auth::user()->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $id)
            ->first() ?? abort(404, '退款单不存在');
    }
}
