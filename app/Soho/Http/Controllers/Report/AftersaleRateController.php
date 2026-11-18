<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 售后率报表控制器
 * 展示各店铺的退款率、退货率、投诉率，支持时间范围筛选
 */
class AftersaleRateController extends Controller
{
    /**
     * 售后率列表
     * 按店铺维度计算售后率指标，支持时间范围筛选
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

        // 按店铺统计订单数与售后数
        $report = DB::table('soho_shops as s')
            ->leftJoin('soho_platform_orders as o', function ($join) use ($tenantId, $startDate, $endDate) {
                $join->on('s.id', '=', 'o.shop_id')
                    ->where('o.tenant_id', $tenantId)
                    ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->leftJoin('soho_order_after_sales as a', function ($join) use ($tenantId, $startDate, $endDate) {
                $join->on('s.id', '=', 'a.shop_id')
                    ->where('a.tenant_id', $tenantId)
                    ->whereBetween('a.apply_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->where('s.tenant_id', $tenantId)
            ->when($platform, fn($q) => $q->where('s.platform', $platform))
            ->select(
                's.id',
                's.shop_name',
                's.platform',
                DB::raw('COUNT(DISTINCT o.id) as order_count'),
                // 退款数
                DB::raw('COUNT(DISTINCT CASE WHEN a.type = \'refund\' THEN a.id END) as refund_count'),
                // 退货数
                DB::raw('COUNT(DISTINCT CASE WHEN a.type = \'return\' THEN a.id END) as return_count'),
                // 投诉/争议数
                DB::raw('COUNT(DISTINCT CASE WHEN a.type = \'dispute\' THEN a.id END) as dispute_count')
            )
            ->groupBy('s.id', 's.shop_name', 's.platform')
            ->orderByDesc('order_count')
            ->paginate(20);

        // 计算各售后率指标
        $report->getCollection()->transform(function ($item) {
            // 退款率 = 退款数 / 订单数 * 100%
            $item->refund_rate = $item->order_count > 0
                ? round($item->refund_count / $item->order_count * 100, 2)
                : 0;
            // 退货率 = 退货数 / 订单数 * 100%
            $item->return_rate = $item->order_count > 0
                ? round($item->return_count / $item->order_count * 100, 2)
                : 0;
            // 投诉率 = 投诉数 / 订单数 * 100%
            $item->dispute_rate = $item->order_count > 0
                ? round($item->dispute_count / $item->order_count * 100, 2)
                : 0;
            return $item;
        });

        // 汇总统计
        $stats = [
            'total_orders'     => $report->sum('order_count'),
            'total_refund'     => $report->sum('refund_count'),
            'total_return'     => $report->sum('return_count'),
            'total_dispute'    => $report->sum('dispute_count'),
            'avg_refund_rate'  => $report->count() > 0 ? round($report->avg('refund_rate'), 2) : 0,
            'avg_return_rate'  => $report->count() > 0 ? round($report->avg('return_rate'), 2) : 0,
            'avg_dispute_rate' => $report->count() > 0 ? round($report->avg('dispute_rate'), 2) : 0,
        ];

        return view('report.operation.aftersale-rate', compact('report', 'stats', 'platformOptions', 'startDate', 'endDate'));
    }
}
