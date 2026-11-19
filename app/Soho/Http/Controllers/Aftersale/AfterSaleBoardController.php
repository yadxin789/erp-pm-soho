<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 售后看板（申请登记 / 待补充资料 / 进度 / 沟通记录 / 操作记录）
 *
 * 口径：
 * - 数据源为 soho_order_after_sales（平台回传的售后单）；
 * - 所有读写限定在当前租户内；
 * - 沟通记录与操作记录统一落 soho_operation_logs，model_type 固定 Aftersale。
 */
class AfterSaleBoardController extends Controller
{
    private const LOG_MODEL = 'Aftersale';

    /** 售后申请登记 */
    public function apply(Request $request)
    {
        $rows = $this->baseQuery($request)->orderByDesc('a.id')->paginate(15)->withQueryString();

        $stats = [
            'total' => $this->baseQuery($request)->count(),
            'pending' => $this->baseQuery($request)->where('a.status', 'pending')->count(),
            'amount' => (float) $this->baseQuery($request)->sum('a.refund_amount'),
        ];

        // 可发起售后的订单：已发货 / 已完成
        $orders = PlatformOrder::query()
            ->when(Auth::user()->tenant_id, fn ($q, $tenant) => $q->where('tenant_id', $tenant))
            ->whereIn('order_status', ['shipped', 'completed'])
            ->orderByDesc('order_created_at')
            ->limit(50)
            ->get(['id', 'system_order_no', 'buyer_nick', 'pay_amount', 'order_status']);

        $types = ['refund' => '仅退款', 'return' => '退货退款', 'exchange' => '换货'];

        return view('soho.aftersale.apply', compact('rows', 'stats', 'orders', 'types'));
    }

    /** 新登记一条售后申请 */
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'type' => ['required', 'in:refund,return,exchange'],
            'reason' => ['required', 'string', 'max:120'],
            'refund_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $tenantId = Auth::user()->tenant_id;

        $order = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($data['order_id']);

        $no = 'AS'.now()->format('YmdHis').random_int(100, 999);

        DB::table('soho_order_after_sales')->insert([
            'tenant_id' => $tenantId,
            'platform_order_id' => $order->id,
            'platform' => $order->platform,
            'shop_id' => $order->shop_id,
            'after_sale_no' => $no,
            'type' => $data['type'],
            'status' => 'pending',
            'refund_amount' => $data['refund_amount'],
            'reason' => $data['reason'],
            'buyer_nick' => $order->buyer_nick,
            'description' => $data['description'] ?? null,
            'apply_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('soho_order_after_sales')->where('after_sale_no', $no)->value('id');

        $this->log($tenantId, 'aftersale.apply', $id, '登记售后申请 '.$no.'（'.$order->system_order_no.'）');

        return redirect()->route('soho.aftersale.apply')->with('success', '已登记售后申请 '.$no.'。');
    }

    /** 待补充资料：说明为空或金额为 0 的售后单 */
    public function supplement(Request $request)
    {
        $rows = $this->baseQuery($request)
            ->where(function ($q) {
                $q->whereNull('a.description')
                    ->orWhere('a.description', '')
                    ->orWhere('a.refund_amount', '<=', 0);
            })
            ->orderByDesc('a.id')
            ->paginate(15)
            ->withQueryString();

        $missing = $this->baseQuery($request)
            ->where(function ($q) {
                $q->whereNull('a.description')->orWhere('a.description', '');
            })
            ->count();

        $noAmount = $this->baseQuery($request)->where('a.refund_amount', '<=', 0)->count();

        return view('soho.aftersale.supplement', compact('rows', 'missing', 'noAmount'));
    }

    /** 补录说明 */
    public function saveSupplement(Request $request)
    {
        $data = $request->validate([
            'after_sale_id' => ['required', 'integer'],
            'description' => ['required', 'string', 'max:500'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tenantId = Auth::user()->tenant_id;

        $row = DB::table('soho_order_after_sales')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $data['after_sale_id'])
            ->first();

        if (! $row) {
            return back()->with('error', '售后单不存在。');
        }

        DB::table('soho_order_after_sales')->where('id', $row->id)->update([
            'description' => $data['description'],
            'refund_amount' => $data['refund_amount'] ?? $row->refund_amount,
            'updated_at' => now(),
        ]);

        $this->log($tenantId, 'aftersale.supplement', $row->id, '补充售后资料 '.$row->after_sale_no);

        return redirect()->route('soho.aftersale.supplement')->with('success', '已补充 '.$row->after_sale_no.' 的售后资料。');
    }

    /** 售后进度 */
    public function progress(Request $request)
    {
        $status = $request->input('status');

        $rows = $this->baseQuery($request)
            ->when($status, fn ($q, $value) => $q->where('a.status', $value))
            ->orderByDesc('a.apply_time')
            ->paginate(15)
            ->withQueryString();

        $byStatus = $this->baseQuery($request)
            ->select('a.status', DB::raw('count(*) as c'))
            ->groupBy('a.status')
            ->pluck('c', 'status');

        return view('soho.aftersale.progress', compact('rows', 'byStatus'));
    }

    /** 售后沟通记录 */
    public function communication(Request $request)
    {
        $rows = $this->baseQuery($request)->orderByDesc('a.id')->paginate(10)->withQueryString();

        $logs = DB::table('soho_operation_logs')
            ->when(Auth::user()->tenant_id, fn ($q, $tenant) => $q->where('tenant_id', $tenant))
            ->where('model_type', self::LOG_MODEL)
            ->where('action', 'aftersale.communication')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('soho.aftersale.communication', compact('rows', 'logs'));
    }

    /** 新增一条沟通记录 */
    public function storeCommunication(Request $request)
    {
        $data = $request->validate([
            'after_sale_id' => ['required', 'integer'],
            'content' => ['required', 'string', 'max:500'],
            'channel' => ['nullable', 'string', 'max:20'],
        ]);

        $tenantId = Auth::user()->tenant_id;

        $row = DB::table('soho_order_after_sales')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $data['after_sale_id'])
            ->first();

        if (! $row) {
            return back()->with('error', '售后单不存在。');
        }

        $channel = $data['channel'] ?: '站内信';
        $this->log(
            $tenantId,
            'aftersale.communication',
            $row->id,
            '【'.$channel.'】'.mb_substr($data['content'], 0, 200).'（'.$row->after_sale_no.'）'
        );

        return redirect()->route('soho.aftersale.communication')->with('success', '沟通记录已保存。');
    }

    /** 售后操作记录 */
    public function operationLog(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $logs = DB::table('soho_operation_logs')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('model_type', self::LOG_MODEL)
            ->when($request->input('action'), fn ($q, $action) => $q->where('action', $action))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $actions = DB::table('soho_operation_logs')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('model_type', self::LOG_MODEL)
            ->select('action', DB::raw('count(*) as c'))
            ->groupBy('action')
            ->pluck('c', 'action');

        return view('soho.aftersale.operation-log', compact('logs', 'actions'));
    }

    /** 售后单基础查询（带订单信息） */
    private function baseQuery(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        return DB::table('soho_order_after_sales as a')
            ->leftJoin('soho_platform_orders as o', 'o.id', '=', 'a.platform_order_id')
            ->when($tenantId, fn ($q) => $q->where('a.tenant_id', $tenantId))
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('a.after_sale_no', 'like', "%{$search}%")
                        ->orWhere('a.buyer_nick', 'like', "%{$search}%")
                        ->orWhere('o.system_order_no', 'like', "%{$search}%")
                        ->orWhere('a.reason', 'like', "%{$search}%");
                });
            })
            ->select(
                'a.id',
                'a.after_sale_no',
                'a.type',
                'a.status',
                'a.refund_amount',
                'a.reason',
                'a.buyer_nick',
                'a.description',
                'a.apply_time',
                'a.handle_time',
                'a.handle_result',
                'o.system_order_no',
                'o.platform_order_no',
                'o.order_status'
            );
    }

    private function log(?int $tenantId, string $action, int $modelId, string $description): void
    {
        // P2-3：日志统一写 Core operation_logs（module=soho）
        DB::table('operation_logs')->insert([
            'module' => 'soho',
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => self::LOG_MODEL,
            'target_id' => $modelId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 200),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
