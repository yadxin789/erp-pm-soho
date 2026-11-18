<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SKU分析控制器
 * 展示SKU的销售数据：SKU、商品名称、销量、销售额、利润率
 */
class SkuAnalysisController extends Controller
{
    /**
     * SKU分析列表
     * 按SKU维度统计销量、销售额、利润率，支持时间范围与商品名称筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 商品名称/SKU关键词搜索
        $keyword = $request->input('keyword');

        // 按SKU维度统计销售数据（关联订单、商品信息）
        $report = DB::table('soho_order_items as oi')
            ->leftJoin('soho_platform_orders as o', 'oi.order_id', '=', 'o.id')
            ->leftJoin('soho_product_skus as ps', 'oi.sku_id', '=', 'ps.id')
            ->leftJoin('soho_products as p', 'ps.product_id', '=', 'p.id')
            ->where('oi.tenant_id', $tenantId)
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('ps.sku_code', 'like', "%{$keyword}%")
                        ->orWhere('p.name', 'like', "%{$keyword}%")
                        ->orWhere('oi.product_name', 'like', "%{$keyword}%");
                });
            })
            ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'ps.id as sku_id',
                'ps.sku_code',
                'p.name as product_name',
                'oi.sku_name',
                // 销量
                DB::raw('SUM(oi.quantity) as sold_quantity'),
                // 销售额
                DB::raw('SUM(oi.quantity * oi.unit_price - oi.discount_amount) as sales_amount'),
                // 成本
                DB::raw('SUM(oi.quantity * oi.cost_price) as cost_amount'),
                // 利润
                DB::raw('SUM(oi.profit_amount) as profit_amount')
            )
            ->groupBy('ps.id', 'ps.sku_code', 'p.name', 'oi.sku_name')
            ->orderByDesc('sales_amount')
            ->paginate(20);

        // 计算每个SKU的利润率
        $report->getCollection()->transform(function ($item) {
            // 利润率 = 利润 / 销售额 * 100%
            $item->profit_margin = $item->sales_amount > 0
                ? round($item->profit_amount / $item->sales_amount * 100, 2)
                : 0;
            return $item;
        });

        // 汇总统计
        $stats = [
            'total_skus'      => $report->total(),
            'total_quantity'  => $report->sum('sold_quantity'),
            'total_sales'     => $report->sum('sales_amount'),
            'total_profit'    => $report->sum('profit_amount'),
            'avg_profit_rate' => $report->count() > 0 ? round($report->avg('profit_margin'), 2) : 0,
        ];

        return view('report.data.sku-analysis', compact('report', 'stats', 'startDate', 'endDate'));
    }
}
