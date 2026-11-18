<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Shop;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Product;
use App\Soho\Models\Inventory;
use App\Soho\Models\User;
use App\Soho\Models\StockSyncLog;
use App\Soho\Models\SyncTaskLog;
use App\Soho\Models\SystemGoods;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 显示仪表盘
     */
    public function index()
    {
        $user = auth()->user();

        // 统计概览数据
        $stats = [
            'total_shops' => Shop::count(),
            'total_orders' => PlatformOrder::count(),
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'low_stock_count' => Inventory::whereColumn('actual_quantity', '<=', 'safety_quantity')->where('safety_quantity', '>', 0)->count(),
            'pending_aftersale' => \App\Soho\Models\OrderAfterSale::where('status', 'pending')->count(),
        ];

        // 今日订单统计
        $today = now()->toDateString();
        $todayOrders = PlatformOrder::whereDate('order_created_at', $today);
        $stats['today_orders'] = $todayOrders->count();
        $stats['today_sales'] = $todayOrders->sum('total_amount');

        // 昨日订单统计
        $yesterday = now()->subDay()->toDateString();
        $yesterdayOrders = PlatformOrder::whereDate('order_created_at', $yesterday);
        $stats['yesterday_orders'] = $yesterdayOrders->count();
        $stats['yesterday_sales'] = $yesterdayOrders->sum('total_amount');

        // 近7天订单趋势
        $last7Days = PlatformOrder::select(DB::raw('DATE(order_created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(total_amount) as amount'))
            ->where('order_created_at', '>=', now()->subDays(7))
            ->whereNotNull('order_created_at')
            ->groupBy(DB::raw('DATE(order_created_at)'))
            ->orderBy('date')
            ->get();

        // 各平台订单分布
        $platformOrderStats = PlatformOrder::select('platform', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as amount'))
            ->groupBy('platform')
            ->get();

        // 库存预警
        $lowStockItems = Inventory::with(['sku', 'warehouse'])
            ->whereColumn('actual_quantity', '<=', 'safety_quantity')
            ->where('safety_quantity', '>', 0)
            ->orderBy('actual_quantity', 'asc')
            ->limit(10)
            ->get();

        // 最近订单
        $recentOrders = PlatformOrder::with(['shop'])
            ->orderBy('order_created_at', 'desc')
            ->limit(10)
            ->get();

        // 同步任务状态
        $syncStatus = [
            'last_order_sync' => SyncTaskLog::where('task_type', 'order')->latest('created_at')->first(),
            'last_stock_sync' => SyncTaskLog::where('task_type', 'stock')->latest('created_at')->first(),
            'stock_sync_logs' => StockSyncLog::latest('created_at')->limit(5)->get(),
        ];

        return view('dashboard.index', compact('stats', 'last7Days', 'platformOrderStats', 'lowStockItems', 'recentOrders', 'syncStatus'));
    }

    /**
     * 销售报表页面
     */
    public function salesReport(Request $request)
    {
        $shops = Shop::where('status', true)->orderBy('shop_name')->get();
        $platforms = ['taobao', 'jd', 'pdd', 'amazon'];

        // 日期范围过滤
        $dateStart = $request->input('date_start', now()->startOfMonth()->toDateString());
        $dateEnd = $request->input('date_end', now()->endOfMonth()->toDateString());

        $ordersQuery = PlatformOrder::with(['shop'])
            ->whereBetween('order_created_at', [$dateStart . ' 00:00:00', $dateEnd . ' 23:59:59'])
            ->whereNotNull('order_created_at');

        if ($request->shop_id) {
            $ordersQuery->where('shop_id', $request->shop_id);
        }
        if ($request->platform) {
            $ordersQuery->where('platform', $request->platform);
        }

        $orders = $ordersQuery->orderBy('order_created_at', 'desc')->paginate(20);

        // 销售统计
        $salesStats = [
            'total_orders' => $orders->total(),
            'total_sales' => (clone $ordersQuery)->sum('total_amount'),
            'avg_order_amount' => (clone $ordersQuery)->avg('total_amount'),
        ];

        // 按平台统计
        $platformSales = PlatformOrder::select('platform', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->whereBetween('order_created_at', [$dateStart . ' 00:00:00', $dateEnd . ' 23:59:59'])
            ->whereNotNull('order_created_at')
            ->groupBy('platform')
            ->get();

        // 按日期统计（用于图表）
        $dailySales = PlatformOrder::select(DB::raw('DATE(order_created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(total_amount) as amount'))
            ->whereBetween('order_created_at', [$dateStart . ' 00:00:00', $dateEnd . ' 23:59:59'])
            ->whereNotNull('order_created_at')
            ->groupBy(DB::raw('DATE(order_created_at)'))
            ->orderBy('date')
            ->get();

        return view('dashboard.sales-report', compact('orders', 'shops', 'platforms', 'salesStats', 'platformSales', 'dailySales', 'dateStart', 'dateEnd'));
    }

    /**
     * 库存概览页面
     */
    public function inventoryOverview(Request $request)
    {
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        $inventory = Inventory::with(['sku', 'warehouse'])
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->low_stock, function($query) {
                $query->whereColumn('actual_quantity', '<=', 'safety_quantity')
                    ->where('safety_quantity', '>', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // 库存统计
        $inventoryStats = [
            'total_items' => Inventory::count(),
            'total_quantity' => Inventory::sum('actual_quantity'),
            'low_stock_count' => Inventory::whereColumn('actual_quantity', '<=', 'safety_quantity')->where('safety_quantity', '>', 0)->count(),
            'out_of_stock_count' => Inventory::where('actual_quantity', 0)->count(),
            'total_value' => Inventory::sum(DB::raw('actual_quantity * (SELECT cost_price FROM soho_product_skus WHERE soho_product_skus.id = soho_inventories.sku_id LIMIT 1)')),
        ];

        // 按仓库统计
        $warehouseStats = Inventory::select('warehouse_id', DB::raw('count(*) as items'), DB::raw('sum(actual_quantity) as total_qty'))
            ->groupBy('warehouse_id')
            ->with('warehouse')
            ->get();

        // 低库存预警
        $lowStockAlerts = Inventory::with(['sku', 'warehouse'])
            ->whereColumn('actual_quantity', '<=', 'safety_quantity')
            ->where('safety_quantity', '>', 0)
            ->orderBy('actual_quantity', 'asc')
            ->limit(15)
            ->get();

        return view('dashboard.inventory-overview', compact('inventory', 'warehouses', 'inventoryStats', 'warehouseStats', 'lowStockAlerts'));
    }

    /**
     * 商品排名页面
     */
    public function productRanking(Request $request)
    {
        $sortBy = $request->input('sort_by', 'sales');
        $period = $request->input('period', 'month');

        // 时间范围
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        // 基于订单的商品排名（通过 order_items → product_skus → products 链）
        $productRankings = PlatformOrder::select(
                'skus.product_id',
                DB::raw('count(*) as order_count'),
                DB::raw('sum(items.quantity) as total_quantity'),
                DB::raw('sum(items.unit_price * items.quantity) as total_amount')
            )
            ->join('soho_order_items as items', 'soho_platform_orders.id', '=', 'items.order_id')
            ->leftJoin('soho_product_skus as skus', 'items.sku_id', '=', 'skus.id')
            ->whereBetween('soho_platform_orders.order_created_at', $dateRange)
            ->whereNotNull('soho_platform_orders.order_created_at')
            ->whereNotNull('skus.product_id')
            ->groupBy('skus.product_id')
            ->orderBy($sortBy === 'sales' ? 'total_amount' : 'order_count', 'desc')
            ->limit(50)
            ->get();

        // 获取商品信息
        $productIds = $productRankings->pluck('product_id')->unique();
        $products = Product::with(['brand', 'category'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        // 最热销商品TOP10
        $topProducts = $productRankings->take(10);

        // 分类销售统计（通过 products → product_categories）
        $categoryStats = PlatformOrder::select('categories.name as category', DB::raw('count(*) as count'))
            ->join('soho_order_items as items', 'soho_platform_orders.id', '=', 'items.order_id')
            ->leftJoin('soho_product_skus as skus', 'items.sku_id', '=', 'skus.id')
            ->leftJoin('soho_products', 'skus.product_id', '=', 'soho_products.id')
            ->leftJoin('soho_product_categories as categories', 'soho_products.category_id', '=', 'categories.id')
            ->whereBetween('soho_platform_orders.order_created_at', $dateRange)
            ->whereNotNull('soho_platform_orders.order_created_at')
            ->whereNotNull('categories.name')
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('dashboard.product-ranking', compact('productRankings', 'products', 'topProducts', 'categoryStats', 'sortBy', 'period'));
    }
}