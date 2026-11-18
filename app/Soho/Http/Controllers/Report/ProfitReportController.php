<?php
namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitReportController extends Controller
{
    /**
     * 利润报表页面
     */
    public function index(Request $request)
    {
        // 获取当前用户可见的店铺列表
        $shops = Shop::all();

        // 获取利润统计数据
        $profits = $this->buildProfitQuery($request);

        // 汇总统计
        $summary = [
            'total_revenue' => $profits->sum('revenue'),
            'total_cost' => $profits->sum('cost'),
            'total_profit' => $profits->sum('profit'),
            'profit_margin' => $profits->sum('revenue') > 0
                ? round($profits->sum('profit') / $profits->sum('revenue') * 100, 2)
                : 0,
        ];

        return view('report.profit', compact('profits', 'summary', 'shops'));
    }

    /**
     * 利润报表导出
     */
    public function export(Request $request)
    {
        $profits = $this->buildProfitQuery($request);

        $filename = 'profit_report_' . date('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        $callback = function () use ($profits) {
            $handle = fopen('php://output', 'w');
            // CSV表头
            fputcsv($handle, ['日期', '收入(元)', '成本(元)', '利润(元)', '利润率(%)']);

            foreach ($profits as $item) {
                fputcsv($handle, [
                    $item->date,
                    number_format($item->revenue, 2),
                    number_format($item->cost, 2),
                    number_format($item->profit, 2),
                    number_format($item->profit_margin, 2),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 构建利润统计查询
     */
    private function buildProfitQuery(Request $request)
    {
        // 构建订单成本子查询：按订单ID汇总 order_items 的成本
        $costSubQuery = DB::table('soho_order_items')
            ->select('order_id', DB::raw('SUM(quantity * cost_price) as total_cost'))
            ->groupBy('order_id');

        $query = PlatformOrder::where('order_status', 'completed')
            ->leftJoinSub($costSubQuery, 'costs', function ($join) {
                $join->on('soho_platform_orders.id', '=', 'costs.order_id');
            });

        // 日期范围筛选
        if ($request->start_date) {
            $query->whereDate('order_created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('order_created_at', '<=', $request->end_date);
        }

        // 店铺筛选
        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }

        // 按日期分组统计
        $profits = $query->select(
            DB::raw('DATE(order_created_at) as date'),
            DB::raw('SUM(pay_amount) as revenue'),
            DB::raw('COALESCE(SUM(costs.total_cost), 0) as cost'),
            DB::raw('SUM(pay_amount) - COALESCE(SUM(costs.total_cost), 0) as profit')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 为每行计算利润率
        $profits->transform(function ($item) {
            $item->profit_margin = $item->revenue > 0
                ? round($item->profit / $item->revenue * 100, 2)
                : 0;
            return $item;
        });

        return $profits;
    }
}
