<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 只读角色报表控制器
 * 为只读角色提供订单报表、Listing查看、库存预警三个只读视图
 */
class ReadonlyController extends Controller
{
    /**
     * 订单报表只读视图
     * 展示订单数据的汇总统计与明细列表，支持时间范围与店铺筛选
     */
    public function orderReport(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 店铺筛选
        $shopId = $request->input('shop_id');

        // 获取当前租户下的店铺列表
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 订单查询（只读，仅展示）
        $orders = DB::table('soho_platform_orders as o')
            ->leftJoin('soho_shops as s', 'o.shop_id', '=', 's.id')
            ->where('o.tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('o.shop_id', $shopId))
            ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'o.id',
                'o.platform_order_no',
                'o.platform',
                's.shop_name',
                'o.order_status',
                'o.pay_status',
                'o.total_amount',
                'o.pay_amount',
                'o.order_created_at'
            )
            ->orderByDesc('o.order_created_at')
            ->paginate(20);

        // 汇总统计
        $baseQuery = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->whereBetween('order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $stats = [
            'total_orders' => (clone $baseQuery)->count(),
            'total_sales'  => (clone $baseQuery)->sum('pay_amount'),
            'paid_count'   => (clone $baseQuery)->where('pay_status', 'paid')->count(),
            'completed_count' => (clone $baseQuery)->where('order_status', 'completed')->count(),
        ];

        return view('report.readonly.order-report', compact('orders', 'stats', 'shops', 'startDate', 'endDate'));
    }

    /**
     * Listing 只读查看视图
     * 展示商品在各个平台的 Listing 信息，支持按平台与状态筛选
     */
    public function listingView(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 平台筛选
        $platform = $request->input('platform');

        // 状态筛选
        $status = $request->input('status');

        // 平台选项
        $platformOptions = [
            'taobao' => '淘宝',
            'jd'     => '京东',
            'pdd'    => '拼多多',
            'douyin' => '抖音',
            'amazon' => '亚马逊',
        ];

        // 状态选项
        $statusOptions = [
            'online'   => '在售',
            'offline'  => '下架',
            'review'   => '审核中',
            'rejected' => '审核拒绝',
        ];

        // 查询平台商品（Listing）
        $listings = DB::table('soho_platform_products as pp')
            ->leftJoin('soho_shops as s', 'pp.shop_id', '=', 's.id')
            ->leftJoin('soho_products as p', 'pp.product_id', '=', 'p.id')
            ->where('pp.tenant_id', $tenantId)
            ->when($platform, fn($q) => $q->where('pp.platform', $platform))
            ->when($status, fn($q) => $q->where('pp.status', $status))
            ->select(
                'pp.id',
                'pp.platform',
                'pp.platform_product_id',
                'pp.platform_sku',
                'p.name as product_name',
                's.shop_name',
                'pp.status',
                'pp.price',
                'pp.stock',
                'pp.updated_at'
            )
            ->orderByDesc('pp.updated_at')
            ->paginate(20);

        // 汇总统计
        $baseQuery = DB::table('soho_platform_products')->where('tenant_id', $tenantId);
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'online'   => (clone $baseQuery)->where('status', 'online')->count(),
            'offline'  => (clone $baseQuery)->where('status', 'offline')->count(),
            'review'   => (clone $baseQuery)->where('status', 'review')->count(),
        ];

        return view('report.readonly.listing-view', compact('listings', 'stats', 'platformOptions', 'statusOptions'));
    }

    /**
     * 库存预警只读视图
     * 展示库存预警信息，只读模式，支持按预警级别筛选
     */
    public function inventoryWarning(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 仓库筛选
        $warehouseId = $request->input('warehouse_id');

        // 预警级别筛选
        $level = $request->input('level');

        // 预警级别选项
        $levelOptions = [
            'critical' => '紧急缺货',
            'warning'  => '库存预警',
            'normal'   => '库存正常',
        ];

        // 获取仓库列表
        $warehouses = DB::table('soho_warehouses')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // 查询库存数据（只读）
        $query = DB::table('soho_inventories as i')
            ->leftJoin('soho_product_skus as ps', 'i.sku_id', '=', 'ps.id')
            ->leftJoin('soho_products as p', 'ps.product_id', '=', 'p.id')
            ->leftJoin('soho_warehouses as w', 'i.warehouse_id', '=', 'w.id')
            ->where('i.tenant_id', $tenantId)
            ->when($warehouseId, fn($q) => $q->where('i.warehouse_id', $warehouseId))
            ->when($level === 'critical', fn($q) => $q->where('i.actual_quantity', '<=', 0))
            ->when($level === 'warning', fn($q) => $q->where('i.actual_quantity', '>', 0)->whereColumn('i.actual_quantity', '<=', 'i.safety_quantity')->where('i.safety_quantity', '>', 0))
            ->when($level === 'normal', fn($q) => $q->whereColumn('i.actual_quantity', '>', 'i.safety_quantity')->where('i.safety_quantity', '>', 0))
            ->select(
                'i.id',
                'p.name as product_name',
                'ps.sku_code',
                'ps.name as sku_name',
                'w.name as warehouse_name',
                'i.actual_quantity',
                'i.safety_quantity',
                'i.updated_at'
            );

        $items = $query->orderByDesc('i.updated_at')->paginate(20);

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

        // 汇总统计
        $baseQuery = DB::table('soho_inventories')->where('tenant_id', $tenantId)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));
        $stats = [
            'critical_count' => (clone $baseQuery)->where('actual_quantity', '<=', 0)->count(),
            'warning_count'  => (clone $baseQuery)->where('actual_quantity', '>', 0)->whereColumn('actual_quantity', '<=', 'safety_quantity')->where('safety_quantity', '>', 0)->count(),
            'normal_count'   => (clone $baseQuery)->whereColumn('actual_quantity', '>', 'safety_quantity')->where('safety_quantity', '>', 0)->count(),
            'total_count'    => (clone $baseQuery)->count(),
        ];

        return view('report.readonly.inventory-warning', compact('items', 'stats', 'warehouses', 'levelOptions'));
    }
}
