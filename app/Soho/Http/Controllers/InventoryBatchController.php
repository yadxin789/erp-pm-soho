<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\InventoryBatch;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryBatchController extends Controller
{
    /**
     * 库存批次列表
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();

        $batches = InventoryBatch::with(['tenant', 'warehouse', 'sku'])
            ->when($request->search, function($query) use ($request) {
                $query->where('batch_no', 'like', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->sku_id, function($query) use ($request) {
                $query->where('sku_id', $request->sku_id);
            })
            ->when($request->expiring_soon, function($query) {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', now()->addDays(30));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('inventory.batches', compact('batches', 'warehouses'));
    }
}