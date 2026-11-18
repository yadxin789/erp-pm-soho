<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Inventory;
use App\Soho\Models\InventoryFlow;
use App\Soho\Models\Warehouse;
use App\Soho\Models\ProductSku;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * 库存查询列表
     */
    public function index(Request $request)
    {
        $inventories = Inventory::with(['sku', 'warehouse'])
            ->when($request->search, function($query) use ($request) {
                $query->whereHas('sku', function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('sku_code', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->low_stock, function($query) {
                $query->whereColumn('actual_quantity', '<=', 'safety_quantity')
                    ->where('safety_quantity', '>', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('inventory.index', compact('inventories'));
    }

    /**
     * 导出库存
     */
    public function export(Request $request)
    {
        $inventories = Inventory::with(['sku', 'warehouse'])
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->get();

        $filename = 'inventory_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];
        
        $callback = function() use ($inventories) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['商品名称', 'SKU', '仓库', '库存数量', '预警值', '更新时间']);
            
            foreach ($inventories as $item) {
                fputcsv($handle, [
                    $item->sku->name ?? '',
                    $item->sku->sku_code ?? '',
                    $item->warehouse->name ?? '',
                    $item->actual_quantity,
                    $item->safety_quantity,
                    $item->updated_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 库存流水
     */
    public function flow(Request $request)
    {
        $flows = InventoryFlow::with(['sku', 'warehouse'])
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('inventory.flow', compact('flows'));
    }

    /**
     * 入库管理
     */
    public function inbound(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $skus = ProductSku::orderBy('name')->get();
        $inboundFlows = InventoryFlow::with(['sku', 'warehouse', 'operator'])
            ->where('type', 'in')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('inventory.inbound', compact('warehouses', 'skus', 'inboundFlows'));
    }

    /**
     * 保存入库记录
     */
    public function storeInbound(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:soho_warehouses,id',
            'sku_id' => 'required|exists:soho_product_skus,id',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
            'remark' => 'nullable|string|max:500',
        ]);

        // 创建库存流水
        InventoryFlow::create([
            'flow_no' => 'IN' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'type' => 'in',
            'warehouse_id' => $request->warehouse_id,
            'sku_id' => $request->sku_id,
            'quantity' => $request->quantity,
            'before_quantity' => 0,
            'after_quantity' => 0,
            'reference_no' => $request->reference_no,
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        // 更新库存
        $inventory = Inventory::firstOrCreate(
            ['warehouse_id' => $request->warehouse_id, 'sku_id' => $request->sku_id],
            ['actual_quantity' => 0, 'available_quantity' => 0]
        );
        $inventory->increment('actual_quantity', $request->quantity);
        $inventory->increment('available_quantity', $request->quantity);

        return redirect()->route('soho.inventory.in')->with('success', '入库成功');
    }

    /**
     * 出库管理
     */
    public function outbound(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $skus = ProductSku::orderBy('name')->get();
        $outboundFlows = InventoryFlow::with(['sku', 'warehouse', 'operator'])
            ->where('type', 'out')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('inventory.outbound', compact('warehouses', 'skus', 'outboundFlows'));
    }

    /**
     * 保存出库记录
     */
    public function storeOutbound(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:soho_warehouses,id',
            'sku_id' => 'required|exists:soho_product_skus,id',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
            'remark' => 'nullable|string|max:500',
        ]);

        // 检查库存
        $inventory = Inventory::where('warehouse_id', $request->warehouse_id)
            ->where('sku_id', $request->sku_id)
            ->first();

        if (!$inventory || $inventory->available_quantity < $request->quantity) {
            return back()->with('error', '库存不足');
        }

        // 创建库存流水
        InventoryFlow::create([
            'flow_no' => 'OUT' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'type' => 'out',
            'warehouse_id' => $request->warehouse_id,
            'sku_id' => $request->sku_id,
            'quantity' => $request->quantity,
            'before_quantity' => $inventory->actual_quantity,
            'after_quantity' => $inventory->actual_quantity - $request->quantity,
            'reference_no' => $request->reference_no,
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        // 更新库存
        $inventory->decrement('actual_quantity', $request->quantity);
        $inventory->decrement('available_quantity', $request->quantity);

        return redirect()->route('soho.inventory.out')->with('success', '出库成功');
    }

    /**
     * 库存调拨
     */
    public function transfer(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $skus = ProductSku::orderBy('name')->get();
        $transferFlows = InventoryFlow::with(['sku', 'warehouse', 'operator'])
            ->where('type', 'transfer')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('inventory.transfer', compact('warehouses', 'skus', 'transferFlows'));
    }

    /**
     * 保存调拨记录
     */
    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:soho_warehouses,id',
            'to_warehouse_id' => 'required|exists:soho_warehouses,id|different:from_warehouse_id',
            'sku_id' => 'required|exists:soho_product_skus,id',
            'quantity' => 'required|integer|min:1',
            'remark' => 'nullable|string|max:500',
        ]);

        // 检查源仓库库存
        $fromInventory = Inventory::where('warehouse_id', $request->from_warehouse_id)
            ->where('sku_id', $request->sku_id)
            ->first();

        if (!$fromInventory || $fromInventory->available_quantity < $request->quantity) {
            return back()->with('error', '源仓库库存不足');
        }

        // 扣减源仓库库存
        $fromInventory->decrement('actual_quantity', $request->quantity);
        $fromInventory->decrement('available_quantity', $request->quantity);

        // 增加目标仓库库存
        $toInventory = Inventory::firstOrCreate(
            ['warehouse_id' => $request->to_warehouse_id, 'sku_id' => $request->sku_id],
            ['actual_quantity' => 0, 'available_quantity' => 0]
        );
        $toInventory->increment('actual_quantity', $request->quantity);
        $toInventory->increment('available_quantity', $request->quantity);

        // 创建调拨流水
        InventoryFlow::create([
            'flow_no' => 'TRF' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'type' => 'transfer',
            'warehouse_id' => $request->from_warehouse_id,
            'sku_id' => $request->sku_id,
            'quantity' => $request->quantity,
            'before_quantity' => $fromInventory->actual_quantity + $request->quantity,
            'after_quantity' => $fromInventory->actual_quantity,
            'reference_no' => '调拨至仓库 #' . $request->to_warehouse_id,
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        return redirect()->route('soho.inventory.transfer')->with('success', '调拨成功');
    }

    /**
     * 库存盘点
     */
    public function stocktake(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();
        $skus = ProductSku::orderBy('name')->get();
        $stocktakeFlows = InventoryFlow::with(['sku', 'warehouse', 'operator'])
            ->where('type', 'stocktake')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('inventory.stocktake', compact('warehouses', 'skus', 'stocktakeFlows'));
    }

    /**
     * 保存盘点记录
     */
    public function storeStocktake(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:soho_warehouses,id',
            'items' => 'required|array',
            'items.*.sku_id' => 'required|exists:soho_product_skus,id',
            'items.*.quantity' => 'required|integer|min:0',
            'remark' => 'nullable|string|max:500',
        ]);

        foreach ($request->items as $item) {
            $inventory = Inventory::firstOrCreate(
                ['warehouse_id' => $request->warehouse_id, 'sku_id' => $item['sku_id']],
                ['actual_quantity' => 0, 'available_quantity' => 0]
            );

            $oldQuantity = $inventory->actual_quantity;
            $newQuantity = $item['quantity'];
            $diff = $newQuantity - $oldQuantity;

            // 更新库存
            $inventory->update([
                'actual_quantity' => $newQuantity,
                'available_quantity' => $newQuantity,
            ]);

            // 创建盘点流水
            if ($diff != 0) {
                InventoryFlow::create([
                    'flow_no' => 'STK' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'type' => 'stocktake',
                    'warehouse_id' => $request->warehouse_id,
                    'sku_id' => $item['sku_id'],
                    'quantity' => abs($diff),
                    'before_quantity' => $oldQuantity,
                    'after_quantity' => $newQuantity,
                    'reference_no' => '盘点调整',
                    'operator_id' => auth()->id(),
                    'remark' => $request->remark,
                ]);
            }
        }

        return redirect()->route('soho.inventory.stocktake')->with('success', '盘点完成');
    }
}
