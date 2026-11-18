<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 店铺分析控制器
 * 展示各店铺的详细经营数据：销售额、订单数、客单价、退款率、转化率等
 */
class ShopAnalysisController extends Controller
{
    /**
     * 店铺分析列表
     * 按店铺维度综合分析经营数据，支持时间范围与平台筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 平台筛选
        $platform = $request->input('platform');

        // 平台选项
        $platformOptions = [
            'taobao' => '淘宝',
            'jd'     => '京东',
            'pdd'    => '拼多多',
            'douyin' => '抖音',
            'amazon' => '亚马逊',
        ];

        // 按店铺统计经营数据
        $report = DB::table('soho_shops as s')
            ->leftJoin('soho_platform_orders as o', function ($join) use ($tenantId, $startDate, $endDate) {
                $join->on('s.id', '=', 'o.shop_id')
                    ->where('o.tenant_id', $tenantId)
                    ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->leftJoin('soho_order_after_sales as a', function ($join) use ($tenantId, $startDate, $endDate) {
                $join->on('s.id', '=', 'a.shop_id')
                    ->where('a.tenant_id', $tenantId)
                    ->whereBetween('a.apply_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->where('a.type', 'refund');
            })
            ->where('s.tenant_id', $tenantId)
            ->when($platform, fn($q) => $q->where('s.platform', $platform))
            ->select(
                's.id',
                's.shop_name',
                's.platform',
                // 订单总数
                DB::raw('COUNT(DISTINCT o.id) as order_count'),
                // 销售额
                DB::raw('COALESCE(SUM(o.pay_amount), 0) as sales_amount'),
                // 已支付订单数
                DB::raw('SUM(CASE WHEN o.pay_status = \'paid\' THEN 1 ELSE 0 END) as paid_count'),
                // 已完成订单数
                DB::raw('SUM(CASE WHEN o.order_status = \'completed\' THEN 1 ELSE 0 END) as completed_count'),
                // 退款数与退款金额
                DB::raw('COUNT(DISTINCT a.id) as refund_count'),
                DB::raw('COALESCE(SUM(a.refund_amount), 0) as refund_amount')
            )
            ->groupBy('s.id', 's.shop_name', 's.platform')
            ->orderByDesc('sales_amount')
            ->paginate(20);

        // 计算各店铺的关键指标
        $report->getCollection()->transform(function ($item) {
            // 客单价
            $item->avg_order_value = $item->order_count > 0
                ? round($item->sales_amount / $item->order_count, 2)
                : 0;
            // 支付转化率
            $item->conversion_rate = $item->order_count > 0
                ? round($item->paid_count / $item->order_count * 100, 2)
                : 0;
            // 退款率
            $item->refund_rate = $item->order_count > 0
                ? round($item->refund_count / $item->order_count * 100, 2)
                : 0;
            // 完成率
            $item->completion_rate = $item->order_count > 0
                ? round($item->completed_count / $item->order_count * 100, 2)
                : 0;
            return $item;
        });

        // 汇总统计
        $stats = [
            'total_shops'    => $report->total(),
            'total_orders'   => $report->sum('order_count'),
            'total_sales'    => $report->sum('sales_amount'),
            'total_refund'   => $report->sum('refund_amount'),
            'avg_aov'        => $report->count() > 0 ? round($report->avg('avg_order_value'), 2) : 0,
        ];

        return view('report.data.shop-analysis', compact('report', 'stats', 'platformOptions', 'startDate', 'endDate'));
    }
}
