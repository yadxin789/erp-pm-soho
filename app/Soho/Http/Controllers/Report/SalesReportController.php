<?php
namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /**
     * 销售报表
     */
    public function index(Request $request)
    {
        $query = PlatformOrder::query();

        // 日期范围筛选
        if ($request->date_start) {
            $query->whereDate('order_created_at', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->whereDate('order_created_at', '<=', $request->date_end);
        }

        // 店铺筛选
        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }

        // 平台筛选
        if ($request->platform) {
            $query->where('platform', $request->platform);
        }

        // 按日期分组统计
        $sales = $query->select(
            DB::raw('DATE(order_created_at) as date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(pay_amount) as sales_amount'),
            DB::raw("SUM(CASE WHEN order_status = 'cancelled' THEN pay_amount ELSE 0 END) as refund_amount")
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 汇总统计
        $summary = [
            'total_orders' => $sales->sum('order_count'),
            'total_sales' => $sales->sum('sales_amount'),
            'avg_order_value' => $sales->sum('order_count') > 0 
                ? round($sales->sum('sales_amount') / $sales->sum('order_count'), 2) 
                : 0,
        ];

        return view('report.sales', compact('sales', 'summary'));
    }

    /**
     * 销售报表导出
     */
    public function export(Request $request)
    {
        $query = PlatformOrder::query();

        // 日期范围筛选
        if ($request->date_start) {
            $query->whereDate('order_created_at', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->whereDate('order_created_at', '<=', $request->date_end);
        }

        // 店铺筛选
        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }

        // 平台筛选
        if ($request->platform) {
            $query->where('platform', $request->platform);
        }

        $orders = $query->select(
            DB::raw('DATE(order_created_at) as date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(pay_amount) as sales_amount'),
            DB::raw("SUM(CASE WHEN order_status = 'cancelled' THEN pay_amount ELSE 0 END) as refund_amount")
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $filename = 'sales_report_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function() use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日期', '订单数', '销售额', '退款金额']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->date,
                    $order->order_count,
                    $order->sales_amount,
                    $order->refund_amount,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
