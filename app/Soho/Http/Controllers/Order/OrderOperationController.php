<?php

namespace App\Soho\Http\Controllers\Order;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 订单操作（拆分 / 合并 / 缺货提醒）
 *
 * 口径：
 * - 只对「待处理 / 已付款」状态的订单做拆分与合并；
 * - 拆分：把选中的明细行搬到新建的子订单，父/子订单金额都按明细重算；
 * - 合并：把被合并订单的明细行搬到目标订单，被合并订单置为 cancelled 并写备注；
 * - 所有操作限定在当前租户内。
 */
class OrderOperationController extends Controller
{
    /** 可操作的订单状态 */
    private const OPEN_STATUSES = ['pending', 'paid'];

    /** 缺货提醒：按订单明细统计可用库存缺口 */
    public function shortage(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $items = DB::table('soho_order_items as i')
            ->join('soho_platform_orders as o', 'o.id', '=', 'i.order_id')
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('i.tenant_id', $tenantId);
            })
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('o.system_order_no', 'like', "%{$search}%")
                        ->orWhere('o.platform_order_no', 'like', "%{$search}%")
                        ->orWhere('i.product_name', 'like', "%{$search}%")
                        ->orWhere('i.sku_name', 'like', "%{$search}%");
                });
            })
            ->whereIn('o.order_status', self::OPEN_STATUSES)
            ->select(
                'i.id',
                'i.order_id',
                'i.product_name',
                'i.sku_name',
                'i.sku_id',
                'i.quantity',
                'o.system_order_no',
                'o.platform_order_no',
                'o.buyer_nick',
                'o.order_status'
            )
            ->orderByDesc('i.id')
            ->get();

        // 可用库存 = Σ(actual_quantity - locked_quantity)
        $stock = DB::table('soho_inventories')
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->select('sku_id', DB::raw('SUM(actual_quantity - locked_quantity) as available'))
            ->groupBy('sku_id')
            ->pluck('available', 'sku_id');

        $rows = $items->filter(function ($item) use ($stock) {
            $available = (int) ($stock[$item->sku_id] ?? 0);

            return $available < (int) $item->quantity;
        })->map(function ($item) use ($stock) {
            $item->available = (int) ($stock[$item->sku_id] ?? 0);
            $item->shortage = (int) $item->quantity - $item->available;

            return $item;
        })->values();

        $stats = [
            'items' => $rows->count(),
            'orders' => $rows->pluck('order_id')->unique()->count(),
            'quantity' => $rows->sum('shortage'),
        ];

        return view('soho.order.stock-shortage', compact('rows', 'stats'));
    }

    /** 订单拆分：列出可拆分（>=2 条明细）的订单 */
    public function split(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $orders = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('system_order_no', 'like', "%{$search}%")
                        ->orWhere('platform_order_no', 'like', "%{$search}%")
                        ->orWhere('buyer_nick', 'like', "%{$search}%");
                });
            })
            ->whereIn('order_status', self::OPEN_STATUSES)
            ->whereHas('orderItems', function ($q) {
                $q->where('quantity', '>', 0);
            })
            ->withCount('orderItems as items_count')
            ->with(['orderItems'])
            ->orderByDesc('order_created_at')
            ->paginate(10)
            ->withQueryString();

        return view('soho.order.split', compact('orders'));
    }

    /** 执行拆分 */
    public function applySplit(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
        ]);

        $tenantId = Auth::user()->tenant_id;

        $order = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($data['order_id']);

        if (! in_array($order->order_status, self::OPEN_STATUSES, true)) {
            return back()->with('error', '该订单当前状态不允许拆分。');
        }

        $items = DB::table('soho_order_items')
            ->where('order_id', $order->id)
            ->whereIn('id', $data['item_ids'])
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', '请选择要拆出的明细行。');
        }

        // 拆出的明细不能覆盖整单，否则等于复制订单
        $totalItems = DB::table('soho_order_items')->where('order_id', $order->id)->count();
        if ($items->count() >= $totalItems) {
            return back()->with('error', '不能把全部明细都拆出，请至少保留一条明细在原订单。');
        }

        DB::transaction(function () use ($order, $items, $tenantId) {
            $childIndex = DB::table('soho_platform_orders')
                ->where('system_order_no', 'like', $order->system_order_no.'-S%')
                ->count() + 1;

            $childId = DB::table('soho_platform_orders')->insertGetId([
                'platform_order_no' => $order->platform_order_no.'-S'.$childIndex,
                'system_order_no' => $order->system_order_no.'-S'.$childIndex,
                'platform' => $order->platform,
                'shop_id' => $order->shop_id,
                'order_status' => $order->order_status,
                'pay_status' => $order->pay_status,
                'total_amount' => $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity),
                'pay_amount' => $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity),
                'discount_amount' => 0,
                'freight_amount' => 0,
                'currency' => $order->currency,
                'buyer_nick' => $order->buyer_nick,
                'receiver_name' => $order->receiver_name,
                'receiver_phone' => $order->receiver_phone,
                'receiver_province' => $order->receiver_province,
                'receiver_city' => $order->receiver_city,
                'receiver_district' => $order->receiver_district,
                'receiver_address' => $order->receiver_address,
                'order_created_at' => $order->order_created_at,
                'operator_remark' => '由 '.$order->system_order_no.' 拆分生成',
                'tenant_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('soho_order_items')
                ->whereIn('id', $items->pluck('id')->all())
                ->update(['order_id' => $childId, 'updated_at' => now()]);

            // 原订单金额按剩余明细重算
            $remainAmount = DB::table('soho_order_items')
                ->where('order_id', $order->id)
                ->get()
                ->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);

            DB::table('soho_platform_orders')->where('id', $order->id)->update([
                'total_amount' => $remainAmount,
                'pay_amount' => $remainAmount,
                'operator_remark' => trim(($order->operator_remark ?? '').' 已拆出 '.$items->count().' 条明细'),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('soho.order.split')->with('success', '拆分完成：已生成子订单 '.$order->system_order_no.'-S1。');
    }

    /** 订单合并：按买家列出可合并订单 */
    public function merge(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $orders = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('system_order_no', 'like', "%{$search}%")
                        ->orWhere('platform_order_no', 'like', "%{$search}%")
                        ->orWhere('buyer_nick', 'like', "%{$search}%");
                });
            })
            ->whereIn('order_status', self::OPEN_STATUSES)
            ->withCount('orderItems as items_count')
            ->orderBy('buyer_nick')
            ->orderByDesc('order_created_at')
            ->paginate(20)
            ->withQueryString();

        // 同一买家的订单笔数，用于提示可合并
        $byBuyer = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('order_status', self::OPEN_STATUSES)
            ->select('buyer_nick', DB::raw('count(*) as order_count'))
            ->groupBy('buyer_nick')
            ->pluck('order_count', 'buyer_nick');

        return view('soho.order.merge', compact('orders', 'byBuyer'));
    }

    /** 执行合并 */
    public function applyMerge(Request $request)
    {
        $data = $request->validate([
            'target_order_id' => ['required', 'integer'],
            'source_order_ids' => ['required', 'array', 'min:1'],
            'source_order_ids.*' => ['integer'],
        ]);

        $tenantId = Auth::user()->tenant_id;

        $target = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($data['target_order_id']);

        if (! in_array($target->order_status, self::OPEN_STATUSES, true)) {
            return back()->with('error', '目标订单当前状态不允许合并。');
        }

        $sources = PlatformOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('id', array_diff($data['source_order_ids'], [$target->id]))
            ->get();

        if ($sources->isEmpty()) {
            return back()->with('error', '请选择要合并进来的其他订单。');
        }

        if ($sources->pluck('buyer_nick')->unique()->count() > 1
            || $sources->pluck('buyer_nick')->contains(fn ($nick) => $nick !== $target->buyer_nick)) {
            return back()->with('error', '只能合并同一买家的订单。');
        }

        DB::transaction(function () use ($target, $sources) {
            $sourceIds = $sources->pluck('id')->all();

            DB::table('soho_order_items')
                ->whereIn('order_id', $sourceIds)
                ->update(['order_id' => $target->id, 'updated_at' => now()]);

            $amount = DB::table('soho_order_items')
                ->where('order_id', $target->id)
                ->get()
                ->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);

            DB::table('soho_platform_orders')->where('id', $target->id)->update([
                'total_amount' => $amount,
                'pay_amount' => $amount,
                'operator_remark' => trim(($target->operator_remark ?? '').' 合并订单：'.$sources->pluck('system_order_no')->implode(',')),
                'updated_at' => now(),
            ]);

            foreach ($sources as $source) {
                DB::table('soho_platform_orders')->where('id', $source->id)->update([
                    'order_status' => 'cancelled',
                    'operator_remark' => trim(($source->operator_remark ?? '').' 已合并至 '.$target->system_order_no),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('soho.order.merge')->with('success', '合并完成：'.$sources->count().' 笔订单已并入 '.$target->system_order_no.'。');
    }
}
