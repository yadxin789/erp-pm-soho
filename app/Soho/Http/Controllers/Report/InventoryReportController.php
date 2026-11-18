<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\Inventory;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        
        $report = Inventory::with(['sku', 'warehouse'])
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->low_stock, function($query) {
                $query->whereColumn('actual_quantity', '<=', 'safety_quantity')
                    ->where('safety_quantity', '>', 0);
            })
            ->when($request->out_of_stock, function($query) {
                $query->where('actual_quantity', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // 统计
        $stats = [
            'total_items' => Inventory::count(),
            'total_quantity' => Inventory::sum('actual_quantity'),
            'low_stock_count' => Inventory::whereColumn('actual_quantity', '<=', 'safety_quantity')->where('safety_quantity', '>', 0)->count(),
            'out_of_stock_count' => Inventory::where('actual_quantity', 0)->count(),
        ];

        return view('report.inventory', compact('report', 'warehouses', 'stats'));
    }

    public function export(Request $request)
    {
        $inventories = Inventory::with(['sku', 'warehouse'])
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->get();

        $filename = 'inventory_report_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];
        
        $callback = function() use ($inventories) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['商品名称', 'SKU', '仓库', '库存数量', '预警值', '状态', '更新时间']);
            
            foreach ($inventories as $item) {
                $status = $item->actual_quantity == 0 ? '缺货' : ($item->actual_quantity <= $item->safety_quantity ? '库存不足' : '正常');
                fputcsv($handle, [
                    $item->sku->name ?? '',
                    $item->sku->sku_code ?? '',
                    $item->warehouse->name ?? '',
                    $item->actual_quantity,
                    $item->safety_quantity,
                    $status,
                    $item->updated_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}