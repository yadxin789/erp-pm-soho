<?php

namespace App\Soho\Http\Controllers\Warehouse;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 库存单据执行闭环 + 库存调整审批（P1-3）
 *
 * 单据状态机：draft（草稿） → pending（待审批） → approved（已审批） → completed（已执行）
 *             任意非 completed 状态可 cancelled（取消）
 *
 * 执行时按 document_type 处理：
 * - inbound  ：warehouse_id 加库存（写批次 + 事务 inbound + 流水 in）
 * - outbound ：warehouse_id 减库存（批次 FIFO + 事务 outbound + 流水 out）
 * - transfer ：warehouse_id → target_warehouse_id 移库（事务 transfer_out/transfer_in + 流水 transfer）
 * - stocktake：按实盘差异调整（正差补、负差扣，事务 adjustment）
 *
 * 库存调整申请（soho_inventory_adjust_approvals）：pending → approved / rejected，
 * 审批通过时立即按申请数量调整库存并落流水。
 */
class InventoryDocumentFlowController extends Controller
{
    /** 单据详情 */
    public function show(int $id)
    {
        $doc = $this->findDoc($id);

        $items = DB::table('soho_inventory_document_items')->where('document_id', $doc->id)
            ->orderBy('id')->get();

        $warehouse = DB::table('soho_warehouses')->where('id', $doc->warehouse_id)->first();
        $target = $doc->target_warehouse_id
            ? DB::table('soho_warehouses')->where('id', $doc->target_warehouse_id)->first()
            : null;

        $skus = DB::table('soho_product_skus')->where('status', 'active')->orderBy('sku_code')
            ->get(['id', 'sku_code', 'name']);

        $flows = DB::table('soho_inventory_flows')->where('reference_no', $doc->document_no)
            ->orderByDesc('id')->get();

        $operator = $doc->operator_id ? DB::table('users')->where('id', $doc->operator_id)->value('name') : null;
        $approver = $doc->approved_by ? DB::table('users')->where('id', $doc->approved_by)->value('name') : null;

        return view('soho.warehouse.inventory-doc-flow', compact('doc', 'items', 'warehouse', 'target', 'skus', 'flows', 'operator', 'approver'));
    }

    /** 追加明细行 */
    public function addItem(Request $request, int $id)
    {
        $data = $request->validate([
            'sku_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'batch_no' => ['nullable', 'string', 'max:64'],
            'remark' => ['nullable', 'string', 'max:200'],
        ], [], ['sku_id' => 'SKU', 'quantity' => '数量']);

        $doc = $this->findDoc($id);
        if (! in_array($doc->status, ['draft', 'pending'], true)) {
            return back()->with('warning', '只有草稿或待审批的单据可以修改明细。');
        }

        $sku = DB::table('soho_product_skus')->where('id', $data['sku_id'])->first(['id', 'name', 'cost_price']);
        if (! $sku) {
            return back()->with('warning', 'SKU 不存在。');
        }

        DB::table('soho_inventory_document_items')->insert([
            'tenant_id' => $doc->tenant_id,
            'document_id' => $doc->id,
            'sku_id' => $sku->id,
            'product_name' => $sku->name,
            'quantity' => (float) $data['quantity'],
            'unit_cost' => (float) ($data['unit_cost'] ?? $sku->cost_price ?? 0),
            'batch_no' => $data['batch_no'] ?? null,
            'remark' => $data['remark'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncTotals($doc->id);

        return back()->with('success', '明细已追加。');
    }

    /** 删除明细行 */
    public function removeItem(int $id, int $itemId)
    {
        $doc = $this->findDoc($id);
        if (! in_array($doc->status, ['draft', 'pending'], true)) {
            return back()->with('warning', '只有草稿或待审批的单据可以修改明细。');
        }

        DB::table('soho_inventory_document_items')->where('document_id', $doc->id)->where('id', $itemId)->delete();
        $this->syncTotals($doc->id);

        return back()->with('success', '明细已删除。');
    }

    /** 提交审批 */
    public function submit(int $id)
    {
        $doc = $this->findDoc($id);
        if ($doc->status !== 'draft') {
            return back()->with('warning', '只有草稿状态的单据可以提交审批。');
        }
        if (! DB::table('soho_inventory_document_items')->where('document_id', $doc->id)->exists()) {
            return back()->with('warning', '单据没有明细行，无法提交审批。');
        }

        DB::table('soho_inventory_documents')->where('id', $doc->id)
            ->update(['status' => 'pending', 'updated_at' => now()]);

        return back()->with('success', '已提交审批。');
    }

    /** 审批通过 */
    public function approve(int $id)
    {
        $doc = $this->findDoc($id);
        if ($doc->status !== 'pending') {
            return back()->with('warning', '只有待审批状态的单据可以审批。');
        }

        DB::table('soho_inventory_documents')->where('id', $doc->id)->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', '审批通过，可执行库存变动。');
    }

    /** 执行单据（库存变动落账） */
    public function execute(int $id)
    {
        $doc = $this->findDoc($id);
        if ($doc->status !== 'approved') {
            return back()->with('warning', '只有已审批的单据可以执行。');
        }

        $items = DB::table('soho_inventory_document_items')->where('document_id', $doc->id)->get();
        if ($items->isEmpty()) {
            return back()->with('warning', '单据没有明细行，无法执行。');
        }

        // 出库/调拨先做可用量预检
        if (in_array($doc->document_type, ['outbound', 'transfer'], true)) {
            $short = [];
            foreach ($items as $i) {
                $avail = $this->available((int) $doc->warehouse_id, (int) $i->sku_id);
                if ($avail < (float) $i->quantity) {
                    $short[] = ($i->product_name ?: 'SKU#'.$i->sku_id)."（可用 {$avail} / 需 {$i->quantity}）";
                }
            }
            if ($short) {
                return back()->with('warning', '库存不足，无法执行：'.implode('；', $short));
            }
        }

        if ($doc->document_type === 'transfer' && ! $doc->target_warehouse_id) {
            return back()->with('warning', '调拨单缺少目标仓，无法执行。');
        }

        DB::transaction(function () use ($doc, $items) {
            $now = now();

            foreach ($items as $item) {
                $qty = (float) $item->quantity;

                switch ($doc->document_type) {
                    case 'inbound':
                        $this->moveStock((int) $doc->warehouse_id, (int) $item->sku_id, $qty, $doc, $item, 'in', 'inbound', $now);
                        break;

                    case 'outbound':
                        $this->moveStock((int) $doc->warehouse_id, (int) $item->sku_id, -$qty, $doc, $item, 'out', 'outbound', $now);
                        break;

                    case 'transfer':
                        $this->moveStock((int) $doc->warehouse_id, (int) $item->sku_id, -$qty, $doc, $item, 'transfer', 'transfer_out', $now);
                        $this->moveStock((int) $doc->target_warehouse_id, (int) $item->sku_id, $qty, $doc, $item, 'transfer', 'transfer_in', $now);
                        break;

                    case 'stocktake':
                        // 盘点数量即实盘数：以「数量」为实盘，按现有实际数算差异
                        $current = $this->available((int) $doc->warehouse_id, (int) $item->sku_id);
                        $diff = $qty - $current;
                        if (abs($diff) > 0.0001) {
                            $this->moveStock((int) $doc->warehouse_id, (int) $item->sku_id, $diff, $doc, $item, $diff > 0 ? 'in' : 'out', 'adjustment', $now, '盘点差异调整');
                        }
                        break;
                }
            }

            DB::table('soho_inventory_documents')->where('id', $doc->id)->update([
                'status' => 'completed',
                'completed_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', '单据已执行，库存已更新。');
    }

    /** 取消单据 */
    public function cancel(Request $request, int $id)
    {
        $doc = $this->findDoc($id);
        if ($doc->status === 'completed') {
            return back()->with('warning', '已执行的单据不能取消。');
        }

        DB::table('soho_inventory_documents')->where('id', $doc->id)->update([
            'status' => 'cancelled',
            'remark' => $request->input('remark') ?: $doc->remark,
            'updated_at' => now(),
        ]);

        return back()->with('success', '单据已取消。');
    }

    /** 库存调整申请：审批通过并落账 */
    public function approveAdjust(int $id)
    {
        $row = $this->findAdjust($id);
        if ($row->status !== 'pending') {
            return back()->with('warning', '只有待审批的调整申请可以审批。');
        }

        $qty = (float) $row->quantity;
        $current = $this->available((int) $row->warehouse_id, (int) $row->sku_id);
        if ($qty < 0 && $current < abs($qty)) {
            return back()->with('warning', "库存不足，无法调整：可用 {$current}，需扣 ".abs($qty));
        }

        DB::transaction(function () use ($row, $qty) {
            $now = now();
            $before = $this->available((int) $row->warehouse_id, (int) $row->sku_id);

            $this->adjustInventory((int) $row->warehouse_id, (int) $row->sku_id, $qty, $now);

            DB::table('soho_inventory_transactions')->insert([
                'tenant_id' => $row->tenant_id,
                'inventory_id' => $this->inventoryId((int) $row->warehouse_id, (int) $row->sku_id),
                'sku_id' => $row->sku_id,
                'warehouse_id' => $row->warehouse_id,
                'type' => 'adjustment',
                'before_quantity' => $before,
                'change_quantity' => $qty,
                'after_quantity' => $before + $qty,
                'reference_type' => 'adjust_approval',
                'reference_no' => $row->order_no ?: ('ADJ-'.$row->id),
                'operator_id' => Auth::id(),
                'remark' => '库存调整审批 #'.$row->id.($row->reason ? '：'.$row->reason : ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('soho_inventory_flows')->insert([
                'flow_no' => 'FLW-ADJ-'.$row->id.'-'.now()->format('His'),
                'type' => $qty >= 0 ? 'in' : 'out',
                'warehouse_id' => $row->warehouse_id,
                'sku_id' => $row->sku_id,
                'quantity' => abs($qty),
                'before_quantity' => $before,
                'after_quantity' => $before + $qty,
                'reference_no' => $row->order_no ?: ('ADJ-'.$row->id),
                'operator_id' => Auth::id(),
                'remark' => '库存调整审批',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('soho_inventory_adjust_approvals')->where('id', $row->id)->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', '调整申请已通过，库存已更新。');
    }

    /** 库存调整申请：驳回 */
    public function rejectAdjust(Request $request, int $id)
    {
        $row = $this->findAdjust($id);
        if ($row->status !== 'pending') {
            return back()->with('warning', '只有待审批的调整申请可以驳回。');
        }

        DB::table('soho_inventory_adjust_approvals')->where('id', $row->id)->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'remark' => $request->input('remark') ?: $row->remark,
            'updated_at' => now(),
        ]);

        return back()->with('success', '调整申请已驳回。');
    }

    // ---------------------------------------------------------------- 内部方法

    private function findDoc(int $id): object
    {
        return DB::table('soho_inventory_documents')
            ->when(Auth::user()->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $id)
            ->first() ?? abort(404, '库存单据不存在');
    }

    private function findAdjust(int $id): object
    {
        return DB::table('soho_inventory_adjust_approvals')
            ->when(Auth::user()->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $id)
            ->first() ?? abort(404, '调整申请不存在');
    }

    private function syncTotals(int $documentId): void
    {
        $items = DB::table('soho_inventory_document_items')->where('document_id', $documentId)->get();
        DB::table('soho_inventory_documents')->where('id', $documentId)->update([
            'total_quantity' => $items->sum('quantity'),
            'total_amount' => $items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_cost),
            'updated_at' => now(),
        ]);
    }

    /** 可用库存（库存主表口径，缺失回落批次合计） */
    private function available(int $warehouseId, int $skuId): float
    {
        $row = DB::table('soho_inventories')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->first();
        if ($row) {
            return (float) $row->actual_quantity - (float) $row->locked_quantity;
        }

        return (float) DB::table('soho_inventory_batches')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->sum('available_quantity');
    }

    private function inventoryId(int $warehouseId, int $skuId): int
    {
        return (int) (DB::table('soho_inventories')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->value('id') ?? 0);
    }

    /** 调整库存主表数量（正负皆可） */
    private function adjustInventory(int $warehouseId, int $skuId, float $delta, $now): void
    {
        $inv = DB::table('soho_inventories')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->first(['id', 'actual_quantity']);
        if ($inv) {
            DB::table('soho_inventories')->where('id', $inv->id)->update([
                'actual_quantity' => (float) $inv->actual_quantity + $delta,
                'version' => DB::raw('version + 1'),
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * 执行一条库存变动：更新主表 + 批次（入库建批/出库 FIFO）+ 事务 + 流水
     */
    private function moveStock(int $warehouseId, int $skuId, float $delta, object $doc, object $item, string $flowType, string $txType, $now, string $note = ''): void
    {
        $before = $this->available($warehouseId, $skuId);
        $after = $before + $delta;

        $this->adjustInventory($warehouseId, $skuId, $delta, $now);

        if ($delta > 0) {
            // 入库：批次累加（优先匹配同 SKU 现有批次，否则新建）
            $batch = $item->batch_no
                ? DB::table('soho_inventory_batches')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->where('batch_no', $item->batch_no)->first()
                : DB::table('soho_inventory_batches')->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)->orderByDesc('id')->first();

            if ($batch) {
                DB::table('soho_inventory_batches')->where('id', $batch->id)->update([
                    'quantity' => (float) $batch->quantity + $delta,
                    'available_quantity' => (float) $batch->available_quantity + $delta,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('soho_inventory_batches')->insert([
                    'tenant_id' => $doc->tenant_id,
                    'warehouse_id' => $warehouseId,
                    'sku_id' => $skuId,
                    'batch_no' => $item->batch_no ?: ('BAT-'.$doc->document_no.'-'.$skuId),
                    'quantity' => $delta,
                    'available_quantity' => $delta,
                    'unit_cost' => (float) $item->unit_cost,
                    'total_cost' => $delta * (float) $item->unit_cost,
                    'remark' => '单据 '.$doc->document_no.' 入库',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } elseif ($delta < 0) {
            // 出库：批次 FIFO
            $qty = abs($delta);
            $batches = DB::table('soho_inventory_batches')
                ->where('warehouse_id', $warehouseId)->where('sku_id', $skuId)
                ->where('available_quantity', '>', 0)
                ->orderByRaw('COALESCE(expiry_date, production_date) ASC')->orderBy('id')
                ->get(['id', 'available_quantity']);

            foreach ($batches as $b) {
                if ($qty <= 0) {
                    break;
                }
                $take = min($qty, (float) $b->available_quantity);
                DB::table('soho_inventory_batches')->where('id', $b->id)->update([
                    'available_quantity' => DB::raw('available_quantity - '.$take),
                    'updated_at' => $now,
                ]);
                $qty -= $take;
            }
        }

        DB::table('soho_inventory_transactions')->insert([
            'tenant_id' => $doc->tenant_id,
            'inventory_id' => $this->inventoryId($warehouseId, $skuId),
            'sku_id' => $skuId,
            'warehouse_id' => $warehouseId,
            'type' => $txType,
            'before_quantity' => $before,
            'change_quantity' => $delta,
            'after_quantity' => $after,
            'reference_type' => 'inventory_document',
            'reference_no' => $doc->document_no,
            'operator_id' => Auth::id(),
            'remark' => '单据执行：'.$doc->document_no.' '.$note,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('soho_inventory_flows')->insert([
            'flow_no' => 'FLW-'.$doc->document_no.'-'.$warehouseId.'-'.$skuId,
            'type' => $flowType,
            'warehouse_id' => $warehouseId,
            'sku_id' => $skuId,
            'quantity' => abs($delta),
            'before_quantity' => $before,
            'after_quantity' => $after,
            'reference_no' => $doc->document_no,
            'operator_id' => Auth::id(),
            'remark' => $note ?: '单据执行',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
