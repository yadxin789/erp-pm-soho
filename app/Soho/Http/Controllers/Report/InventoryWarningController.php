<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 库存预警报表控制器
 * 展示商品库存预警信息：商品、SKU、当前库存、安全库存、预警级别（critical/warning/normal）
 */
class InventoryWarningController extends Controller
{
    /**
     * 库存预警列表
     * 按预警级别展示库存不足的商品，支持按仓库、预警级别筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 仓库筛选参数
        $warehouseId = $request->input('warehouse_id');

        // 预警级别筛选参数
        $level = $request->input('level');

        // 预警级别选项
        $levelOptions = [
            'critical' => '紧急缺货',
            'warning'  => '库存预警',
            'normal'   => '库存正常',
        ];

        // 获取当前租户下的仓库列表
        $warehouses = DB::table('soho_warehouses')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // 查询库存预警数据（关联商品SKU与仓库信息）
        $query = DB::table('soho_inventories as i')
            ->leftJoin('soho_product_skus as ps', 'i.sku_id', '=', 'ps.id')
            ->leftJoin('soho_products as p', 'ps.product_id', '=', 'p.id')
            ->leftJoin('soho_warehouses as w', 'i.warehouse_id', '=', 'w.id')
            ->where('i.tenant_id', $tenantId)
            ->when($warehouseId, fn($q) => $q->where('i.warehouse_id', $warehouseId))
            ->select(
                'i.id',
                'p.name as product_name',
                'ps.sku_code',
                'ps.name as sku_name',
                'w.name as warehouse_name',
                'i.actual_quantity',
                'i.safety_quantity',
                'i.locked_quantity',
                'i.updated_at'
            );

        // 根据预警级别筛选
        $query->when($level === 'critical', function ($q) {
            // 紧急缺货：实际库存为0
            $q->where('i.actual_quantity', '<=', 0);
        })->when($level === 'warning', function ($q) {
            // 库存预警：实际库存小于等于安全库存且大于0
            $q->where('i.actual_quantity', '>', 0)
              ->where('i.actual_quantity', '<=', 'i.safety_quantity')
              ->where('i.safety_quantity', '>', 0);
        })->when($level === 'normal', function ($q) {
            // 库存正常：实际库存大于安全库存
            $q->whereColumn('i.actual_quantity', '>', 'i.safety_quantity')
              ->where('i.safety_quantity', '>', 0);
        });

        $items = $query->orderByRaw('CAST(i.actual_quantity AS FLOAT) / NULLIF(i.safety_quantity, 0) ASC')
            ->paginate(20);

        // 为每条数据计算预警级别
        $items->getCollection()->transform(function ($item) {
            if ($item->actual_quantity <= 0) {
                $item->warning_level = 'critical';
            } elseif ($item->safety_quantity > 0 && $item->actual_quantity <= $item->safety_quantity) {
                $item->warning_level = 'warning';
            } else {
                $item->warning_level = 'normal';
            }
            return $item;
        });

        // 统计卡片数据
        $baseQuery = DB::table('soho_inventories')->where('tenant_id', $tenantId)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));
        $stats = [
            // 紧急缺货数
            'critical_count' => (clone $baseQuery)->where('actual_quantity', '<=', 0)->count(),
            // 预警数
            'warning_count'  => (clone $baseQuery)->where('actual_quantity', '>', 0)
                ->whereColumn('actual_quantity', '<=', 'safety_quantity')
                ->where('safety_quantity', '>', 0)->count(),
            // 正常数
            'normal_count'   => (clone $baseQuery)->whereColumn('actual_quantity', '>', 'safety_quantity')
                ->where('safety_quantity', '>', 0)->count(),
            // 总SKU数
            'total_count'    => (clone $baseQuery)->count(),
        ];

        return view('report.operation.inventory-warning', compact('items', 'stats', 'warehouses', 'levelOptions'));
    }
}
