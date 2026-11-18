<?php

namespace App\Soho\Http\Controllers\Api\Inventory;

use App\Soho\Models\Inventory;
use App\Soho\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 库存API控制器
 * 提供库存查询、调整、调拨、流水查询等功能
 */
class InventoryController extends Controller
{
    /**
     * 获取库存列表
     */
    public function index(Request $request)
    {
        $inventories = Inventory::query();

        if ($request->has('warehouse_id')) {
            $inventories->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->has('sku_id')) {
            $inventories->where('sku_id', $request->sku_id);
        }

        return response()->json([
            'data' => $inventories->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取库存详情
     */
    public function show(Inventory $inventory)
    {
        return response()->json([
            'data' => $inventory->load(['warehouse', 'sku', 'transactions']),
        ]);
    }

    /**
     * 调整库存
     * 使用事务保证数据一致性，同时记录库存流水
     */
    public function adjust(Request $request, Inventory $inventory)
    {
        $request->validate([
            'change_quantity' => 'required|integer',
            'type' => 'required|string|in:stock_in,stock_out,adjust,damage,return',
            'remark' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:32',
            'reference_no' => 'nullable|string|max:64',
        ]);

        $changeQuantity = $request->change_quantity;
        $type = $request->type;
        $remark = $request->remark ?? '';
        $referenceType = $request->reference_type ?? 'manual';
        $referenceNo = $request->reference_no ?? '';

        // 库存出库时检查是否有足够库存
        if ($changeQuantity < 0) {
            $available = $inventory->actual_quantity - $inventory->locked_quantity;
            if ($available < abs($changeQuantity)) {
                return response()->json([
                    'error' => '库存不足',
                    'available' => $available,
                    'required' => abs($changeQuantity),
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            // 使用行锁防止并发调整导致数据不一致
            $inventory = Inventory::lockForUpdate()->find($inventory->id);

            // 获取调整前的库存数量
            $beforeQuantity = $inventory->actual_quantity;

            // 更新库存数量
            $inventory->actual_quantity = $beforeQuantity + $changeQuantity;
            $inventory->version = ($inventory->version ?? 0) + 1;
            $inventory->save();

            // 获取调整后的库存数量
            $afterQuantity = $inventory->actual_quantity;

            // 记录库存流水
            InventoryTransaction::create([
                'tenant_id' => $inventory->tenant_id,
                'inventory_id' => $inventory->id,
                'sku_id' => $inventory->sku_id,
                'warehouse_id' => $inventory->warehouse_id,
                'type' => $type,
                'before_quantity' => $beforeQuantity,
                'change_quantity' => $changeQuantity,
                'after_quantity' => $afterQuantity,
                'reference_type' => $referenceType,
                'reference_no' => $referenceNo,
                'operator_id' => auth()->id() ?? null,
                'remark' => $remark,
            ]);

            DB::commit();

            Log::info('Inventory adjusted successfully', [
                'inventory_id' => $inventory->id,
                'type' => $type,
                'change_quantity' => $changeQuantity,
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $afterQuantity,
            ]);

            return response()->json([
                'data' => $inventory,
                'message' => '库存调整成功',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Inventory adjust failed', [
                'inventory_id' => $inventory->id,
                'type' => $type,
                'change_quantity' => $changeQuantity,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '库存调整失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 库存调拨
     * 从一个仓库调拨库存到另一个仓库
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|integer',
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'remark' => 'nullable|string|max:255',
        ]);

        $skuId = $request->sku_id;
        $fromWarehouseId = $request->from_warehouse_id;
        $toWarehouseId = $request->to_warehouse_id;
        $quantity = $request->quantity;
        $remark = $request->remark ?? '';

        // 检查源仓库和目标仓库是否相同
        if ($fromWarehouseId === $toWarehouseId) {
            return response()->json([
                'error' => '源仓库和目标仓库不能相同',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 获取源仓库库存
            $fromInventory = Inventory::where('sku_id', $skuId)
                ->where('warehouse_id', $fromWarehouseId)
                ->lockForUpdate()
                ->first();

            if (!$fromInventory) {
                return response()->json([
                    'error' => '源仓库不存在该SKU的库存记录',
                ], 404);
            }

            // 检查源仓库库存是否足够
            $available = $fromInventory->actual_quantity - $fromInventory->locked_quantity;
            if ($available < $quantity) {
                return response()->json([
                    'error' => '源仓库库存不足',
                    'available' => $available,
                    'required' => $quantity,
                ], 400);
            }

            // 获取或创建目标仓库库存
            $toInventory = Inventory::firstOrCreate(
                ['sku_id' => $skuId, 'warehouse_id' => $toWarehouseId],
                [
                    'tenant_id' => $fromInventory->tenant_id,
                    'actual_quantity' => 0,
                    'locked_quantity' => 0,
                    'inbound_quantity' => 0,
                    'in_transit_quantity' => 0,
                    'damaged_quantity' => 0,
                    'return_pending_quantity' => 0,
                    'safety_quantity' => 0,
                    'version' => 1,
                ]
            );

            // 更新源仓库库存（出库）
            $fromBefore = $fromInventory->actual_quantity;
            $fromInventory->actual_quantity -= $quantity;
            $fromInventory->version = ($fromInventory->version ?? 0) + 1;
            $fromInventory->save();

            // 更新目标仓库库存（入库）
            $toBefore = $toInventory->actual_quantity;
            $toInventory->actual_quantity += $quantity;
            $toInventory->version = ($toInventory->version ?? 0) + 1;
            $toInventory->save();

            // 生成调拨单号
            $transferNo = 'TRANS' . date('YmdHis') . rand(1000, 9999);

            // 记录源仓库流水（出库）
            InventoryTransaction::create([
                'tenant_id' => $fromInventory->tenant_id,
                'inventory_id' => $fromInventory->id,
                'sku_id' => $skuId,
                'warehouse_id' => $fromWarehouseId,
                'type' => 'transfer_out',
                'before_quantity' => $fromBefore,
                'change_quantity' => -$quantity,
                'after_quantity' => $fromBefore - $quantity,
                'reference_type' => 'transfer',
                'reference_no' => $transferNo,
                'operator_id' => auth()->id() ?? null,
                'remark' => $remark,
            ]);

            // 记录目标仓库流水（入库）
            InventoryTransaction::create([
                'tenant_id' => $toInventory->tenant_id,
                'inventory_id' => $toInventory->id,
                'sku_id' => $skuId,
                'warehouse_id' => $toWarehouseId,
                'type' => 'transfer_in',
                'before_quantity' => $toBefore,
                'change_quantity' => $quantity,
                'after_quantity' => $toBefore + $quantity,
                'reference_type' => 'transfer',
                'reference_no' => $transferNo,
                'operator_id' => auth()->id() ?? null,
                'remark' => $remark,
            ]);

            DB::commit();

            Log::info('Inventory transferred successfully', [
                'sku_id' => $skuId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'quantity' => $quantity,
                'transfer_no' => $transferNo,
            ]);

            return response()->json([
                'message' => '库存调拨成功',
                'transfer_no' => $transferNo,
                'from_inventory' => $fromInventory,
                'to_inventory' => $toInventory,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Inventory transfer failed', [
                'sku_id' => $skuId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '库存调拨失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取库存流水
     */
    public function transactions(Inventory $inventory)
    {
        return response()->json([
            'data' => $inventory->transactions()->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }
}