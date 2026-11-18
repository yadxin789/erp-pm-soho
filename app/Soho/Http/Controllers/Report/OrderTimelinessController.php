<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTimelinessController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::where('status', 'active')->orderBy('shop_name')->get();
        
        $orders = PlatformOrder::with(['shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->platform, function($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('order_created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('order_created_at', '<=', $request->date_end);
            })
            ->whereNotNull('order_created_at')
            ->orderBy('order_created_at', 'desc')
            ->paginate(20);

        // 获取已发货订单用于时效统计（SQLite 兼容的时间差计算）
        $shippedOrders = PlatformOrder::select('*', DB::raw('TIMESTAMPDIFF(SECOND, order_created_at, ship_time) / 3600.0 as timeliness_hours'))
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->platform, function($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('order_created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('order_created_at', '<=', $request->date_end);
            })
            ->whereNotNull('ship_time')
            ->whereNotNull('order_created_at')
            ->get();

        // 计算时效统计
        $avgTimeliness = $shippedOrders->avg('timeliness_hours') ?? 0;
        $onTimeCount = $shippedOrders->filter(fn($o) => $o->timeliness_hours <= 24)->count();
        $totalShipped = $shippedOrders->count();

        $stats = [
            'total_orders' => $orders->total(),
            'shipped_orders' => $totalShipped,
            'avg_timeliness' => round($avgTimeliness, 2),
            'on_time_rate' => $totalShipped > 0 
                ? round($onTimeCount / $totalShipped * 100, 2)
                : 0,
        ];

        return view('report.order-timeliness', compact('orders', 'shops', 'stats'));
    }

    public function export(Request $request)
    {
        $orders = PlatformOrder::with(['shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->platform, function($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('order_created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('order_created_at', '<=', $request->date_end);
            })
            ->whereNotNull('order_created_at')
            ->get();

        $filename = 'order_timeliness_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];
        
        $callback = function() use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['订单号', '店铺', '平台', '订单金额', '下单时间', '发货时间', '时效(小时)']);
            
            foreach ($orders as $order) {
                $timeliness = $order->ship_time 
                    ? \Carbon\Carbon::parse($order->order_created_at)->diffInHours($order->ship_time)
                    : null;
                fputcsv($handle, [
                    $order->platform_order_no,
                    $order->shop->shop_name ?? '',
                    $order->platform,
                    $order->total_amount,
                    $order->order_created_at,
                    $order->ship_time,
                    $timeliness,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}