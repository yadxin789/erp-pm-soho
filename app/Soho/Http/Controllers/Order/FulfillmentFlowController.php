<?php

namespace App\Soho\Http\Controllers\Order;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 履约单执行闭环（P1-1）
 *
 * 状态机：pending（待拣货） → picked（已拣货） → packed（已打包） → shipped（已发货）
 *         任意未发货状态可 cancelled（取消并释放预留）
 *
 * 发货时的副作用（一次事务内完成）：
 * 1. 订单：order_status=shipped、ship_time、waybill_no、express_company
 * 2. 明细：fulfillment_status=shipped，回填运单号与快递公司
 * 3. 包裹：status=shipped、shipped_at、tracking_number
 * 4. 库存：按仓库批次 FIFO 扣减 available_quantity，写 soho_inventory_transactions（outbound）
 * 5. 流水：写 soho_inventory_flows（out）与 soho_order_status_logs
 */
class FulfillmentFlowController extends Controller
{
    /** 履约单详情 */
    public function show(int $id)
    {
        $f = $this->findOrFail($id);

        $order = DB::table('soho_platform_orders')->where('id', $f->order_id)->first();
        $warehouse = DB::table('soho_warehouses')->where('id', $f->warehouse_id)->first();
        $orderItems = DB::table('soho_order_items')->where('order_id', $f->order_id)
            ->get(['id', 'sku_id', 'product_name', 'sku_name', 'quantity', 'unit_price', 'warehouse_id', 'fulfillment_status', 'waybill_no', 'express_company']);

        // 明细声明的数量（items JSON）
        $declared = [];
        $decoded = json_decode((string) $f->items, true);
        if (is_array($decoded)) {
            foreach ($decoded as $row) {
                if (isset($row['item_id'])) {
                    $declared[(int) $row['item_id']] = (int) ($row['quantity'] ?? 0);
                }
            }
        }

        // 各 SKU 在该仓的可用库存（批次口径）
        $stock = [];
        foreach ($orderItems as $item) {
            if (! $item->sku_id) {
                continue;
            }
            if (! isset($stock[$item->sku_id])) {
                $stock[$item->sku_id] = $this->availableOf((int) $f->warehouse_id, (int) $item->sku_id);
            }
        }

        $packages = DB::table('soho_order_packages')->where('order_id', $f->order_id)
            ->get(['id', 'package_number', 'tracking_number', 'status', 'shipped_at', 'express_company']);

        $statusLogs = DB::table('soho_order_status_logs')->where('order_id', $f->order_id)
            ->orderByDesc('id')->limit(10)
            ->get(['from_status', 'to_status', 'operator_type', 'source', 'remark', 'created_at']);

        $assignee = $f->assignee_id ? DB::table('users')->where('id', $f->assignee_id)->value('name') : null;
        $express = DB::table('soho_express_company')->where('status', 1)->orderBy('sort_order')->get(['id', 'name', 'code']);

        return view('soho.order.fulfillment-flow', compact(
            'f', 'order', 'warehouse', 'orderItems', 'declared', 'stock', 'packages', 'statusLogs', 'assignee', 'express'
        ));
    }

    /** 指派拣货人 */
    public function assign(Request $request, int $id)
    {
        $data = $request->validate([
            'assignee_id' => ['required', 'integer'],
        ], [], ['assignee_id' => '拣货人']);

        $f = $this->findOrFail($id);
        if ($f->status !== 'pending') {
            return back()->with('warning', '只有待拣货状态的履约单可以改派。');
        }

        $user = DB::table('users')->where('id', $data['assignee_id'])->first(['id', 'name']);
        if (! $user) {
            return back()->with('warning', '拣货人不存在。');
        }

        DB::table('soho_fulfillment_orders')->where('id', $id)
            ->update(['assignee_id' => $user->id, 'updated_at' => now()]);

        return back()->with('success', "已指派 {$user->name} 负责本单拣货。");
    }

    /** 拣货完成 */
    public function pick(int $id)
    {
        $f = $this->findOrFail($id);
        if ($f->status !== 'pending') {
            return back()->with('warning', '当前状态不能执行拣货。');
        }

        DB::table('soho_fulfillment_orders')->where('id', $id)
            ->update(['status' => 'picked', 'updated_at' => now()]);

        return back()->with('success', '拣货完成，可进入打包环节。');
    }

    /** 打包完成（生成包裹记录） */
    public function pack(Request $request, int $id)
    {
        $f = $this->findOrFail($id);
        if ($f->status !== 'picked') {
            return back()->with('warning', '只有已拣货的履约单可以打包。');
        }

        $order = DB::table('soho_platform_orders')->where('id', $f->order_id)->first();
        $pkgNo = 'PKG-'.now()->format('YmdHis').rand(10, 99);

        DB::transaction(function () use ($f, $order, $pkgNo) {
            $exists = DB::table('soho_order_packages')->where('order_id', $f->order_id)->exists();
            if (! $exists) {
                DB::table('soho_order_packages')->insert([
                    'tenant_id' => $f->tenant_id,
                    'order_id' => $f->order_id,
                    'package_number' => $pkgNo,
                    'warehouse_id' => $f->warehouse_id,
                    'carrier_id' => null,
                    'tracking_number' => null,
                    'status' => 'packed',
                    'waybill_status' => 'pending',
                    'order_created_at' => $order->order_created_at ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('soho_fulfillment_orders')->where('id', $f->id)
                ->update(['status' => 'packed', 'updated_at' => now()]);
        });

        return back()->with('success', "打包完成，包裹号 {$pkgNo}。");
    }

    /** 发货（扣库存 + 回写订单/明细/包裹） */
    public function ship(Request $request, int $id)
    {
        $data = $request->validate([
            'waybill_no' => ['required', 'string', 'max:64'],
            'express_company' => ['required', 'string', 'max:64'],
        ], [], ['waybill_no' => '运单号', 'express_company' => '快递公司']);

        $f = $this->findOrFail($id);
        if ($f->status !== 'packed') {
            return back()->with('warning', '只有已打包的履约单可以发货。');
        }

        $order = DB::table('soho_platform_orders')->where('id', $f->order_id)->first();
        if (! $order) {
            return back()->with('warning', '关联订单不存在。');
        }

        $items = DB::table('soho_order_items')->where('order_id', $f->order_id)->get();
        if ($items->isEmpty()) {
            return back()->with('warning', '订单没有明细行，无法发货。');
        }

        // 库存可用性预检（库存主表 soho_inventories.actual_quantity，缺货直接拦截，避免发一半再回滚）
        $shortages = [];
        foreach ($items as $item) {
            if (! $item->sku_id) {
                continue;
            }
            $available = $this->availableOf($f->warehouse_id, $item->sku_id);
            if ($available < (float) $item->quantity) {
                $shortages[] = ($item->sku_name ?: 'SKU#'.$item->sku_id)."（可用 {$available} / 需 {$item->quantity}）";
            }
        }
        if ($shortages) {
            return back()->with('warning', '库存不足，无法发货：'.implode('；', $shortages));
        }

        $flowNo = 'FLW-OUT-'.now()->format('YmdHis').rand(10, 99);

        DB::transaction(function () use ($f, $order, $items, $data, $flowNo) {
            $now = now();

            foreach ($items as $item) {
                if (! $item->sku_id) {
                    continue;
                }

                // 库存主表（soho_inventories.actual_quantity）为账实口径，批次表提供 FIFO 明细
                $inv = DB::table('soho_inventories')
                    ->where('warehouse_id', $f->warehouse_id)
                    ->where('sku_id', $item->sku_id)
                    ->first(['id', 'actual_quantity']);
                $before = $inv ? (float) $inv->actual_quantity : $this->availableOf($f->warehouse_id, $item->sku_id);

                if ($inv) {
                    DB::table('soho_inventories')->where('id', $inv->id)->update([
                        'actual_quantity' => DB::raw('actual_quantity - '.(float) $item->quantity),
                        'version' => DB::raw('version + 1'),
                        'updated_at' => $now,
                    ]);
                }

                // 批次 FIFO（先到期先出；无到期日按生产日）
                $qty = (float) $item->quantity;
                $batches = DB::table('soho_inventory_batches')
                    ->where('warehouse_id', $f->warehouse_id)
                    ->where('sku_id', $item->sku_id)
                    ->where('available_quantity', '>', 0)
                    ->orderByRaw('COALESCE(expiry_date, production_date) ASC')
                    ->orderBy('id')
                    ->get(['id', 'available_quantity']);

                foreach ($batches as $batch) {
                    if ($qty <= 0) {
                        break;
                    }
                    $take = min($qty, (float) $batch->available_quantity);
                    DB::table('soho_inventory_batches')->where('id', $batch->id)->update([
                        'available_quantity' => DB::raw('available_quantity - '.$take),
                        'updated_at' => $now,
                    ]);
                    $qty -= $take;
                }

                $after = $before - (float) $item->quantity;

                // 库存事务（交易流水）
                DB::table('soho_inventory_transactions')->insert([
                    'tenant_id' => $f->tenant_id,
                    'inventory_id' => $inv->id ?? 0,
                    'sku_id' => $item->sku_id,
                    'warehouse_id' => $f->warehouse_id,
                    'type' => 'outbound',
                    'before_quantity' => $before,
                    'change_quantity' => -(float) $item->quantity,
                    'after_quantity' => $after,
                    'reference_type' => 'order',
                    'reference_no' => $order->system_order_no,
                    'operator_id' => Auth::id(),
                    'remark' => '履约单 #'.$f->id.' 发货出库',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // 库存流水（简表）
                DB::table('soho_inventory_flows')->insert([
                    'flow_no' => $flowNo.'-'.$item->sku_id,
                    'type' => 'out',
                    'warehouse_id' => $f->warehouse_id,
                    'sku_id' => $item->sku_id,
                    'quantity' => (float) $item->quantity,
                    'before_quantity' => $before,
                    'after_quantity' => $after,
                    'reference_no' => $order->system_order_no,
                    'operator_id' => Auth::id(),
                    'remark' => '履约发货',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('soho_order_items')->where('id', $item->id)->update([
                    'fulfillment_status' => 'shipped',
                    'waybill_no' => $data['waybill_no'],
                    'express_company' => $data['express_company'],
                    'warehouse_id' => $f->warehouse_id,
                    'updated_at' => $now,
                ]);
            }

            // 订单主表
            DB::table('soho_platform_orders')->where('id', $f->order_id)->update([
                'order_status' => 'shipped',
                'ship_time' => $now,
                'waybill_no' => $data['waybill_no'],
                'express_company' => $data['express_company'],
                'updated_at' => $now,
            ]);

            // 包裹
            $pkg = DB::table('soho_order_packages')->where('order_id', $f->order_id)->first();
            if ($pkg) {
                DB::table('soho_order_packages')->where('id', $pkg->id)->update([
                    'status' => 'shipped',
                    'shipped_at' => $now,
                    'tracking_number' => $data['waybill_no'],
                    'express_company' => $data['express_company'],
                    'updated_at' => $now,
                ]);
            }

            // 履约单
            DB::table('soho_fulfillment_orders')->where('id', $f->id)
                ->update(['status' => 'shipped', 'updated_at' => $now]);

            // 状态日志
            DB::table('soho_order_status_logs')->insert([
                'tenant_id' => $f->tenant_id,
                'order_id' => $f->order_id,
                'from_status' => $order->order_status,
                'to_status' => 'shipped',
                'operator_type' => 'user',
                'operator_id' => Auth::id(),
                'source' => 'fulfillment',
                'remark' => '履约单 #'.$f->id.' 发货，运单号 '.$data['waybill_no'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', "发货成功：{$data['express_company']} {$data['waybill_no']}，库存已扣减。");
    }

    /** 取消履约单（释放预留） */
    public function cancel(Request $request, int $id)
    {
        $f = $this->findOrFail($id);
        if ($f->status === 'shipped') {
            return back()->with('warning', '已发货的履约单不能取消。');
        }
        if ($f->status === 'cancelled') {
            return back()->with('warning', '该履约单已取消。');
        }

        $remark = $request->input('remark') ?: '履约单取消';

        DB::transaction(function () use ($f, $remark) {
            DB::table('soho_fulfillment_orders')->where('id', $f->id)
                ->update(['status' => 'cancelled', 'remark' => $remark, 'updated_at' => now()]);

            // 释放该订单的库存预留
            DB::table('soho_inventory_reservations')
                ->when($f->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
                ->where('order_id', $f->order_id)
                ->whereIn('status', ['reserved', 'partially_released'])
                ->update([
                    'status' => 'released',
                    'released_quantity' => DB::raw('quantity'),
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', '履约单已取消，订单库存预留已释放。');
    }

    // ---------------------------------------------------------------- 内部方法

    private function findOrFail(int $id): object
    {
        return DB::table('soho_fulfillment_orders')
            ->when(Auth::user()->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $id)
            ->first() ?? abort(404, '履约单不存在');
    }

    /** 某仓某 SKU 的可用数量：优先库存主表，缺失时回落批次合计 */
    private function availableOf(int $warehouseId, int $skuId): float
    {
        $row = DB::table('soho_inventories')
            ->where('warehouse_id', $warehouseId)
            ->where('sku_id', $skuId)
            ->first(['actual_quantity', 'locked_quantity']);

        if ($row) {
            return (float) $row->actual_quantity - (float) $row->locked_quantity;
        }

        return (float) DB::table('soho_inventory_batches')
            ->where('warehouse_id', $warehouseId)
            ->where('sku_id', $skuId)
            ->sum('available_quantity');
    }
}
