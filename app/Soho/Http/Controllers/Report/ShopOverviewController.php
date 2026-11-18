<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 店铺概览控制器
 * 展示各店铺的核心经营数据：店铺名称、平台、订单数、销售额、退款率、发货及时率，支持按平台筛选
 */
class ShopOverviewController extends Controller
{
    /**
     * 店铺概览列表
     * 按店铺维度汇总订单、销售、退款、发货及时率等核心指标
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 平台筛选参数
        $platform = $request->input('platform');

        // 平台筛选选项
        $platformOptions = [
            'taobao'  => '淘宝',
            'jd'      => '京东',
            'pdd'     => '拼多多',
            'douyin'  => '抖音',
            'amazon'  => '亚马逊',
        ];

        // 查询各店铺的统计数据
        $shops = DB::table('soho_shops as s')
            ->leftJoin('soho_platform_orders as o', function ($join) use ($tenantId) {
                $join->on('s.id', '=', 'o.shop_id')
                    ->where('o.tenant_id', $tenantId);
            })
            ->leftJoin('soho_order_after_sales as a', function ($join) use ($tenantId) {
                $join->on('s.id', '=', 'a.shop_id')
                    ->where('a.tenant_id', $tenantId)
                    ->where('a.type', 'refund');
            })
            ->where('s.tenant_id', $tenantId)
            ->when($platform, fn($q) => $q->where('s.platform', $platform))
            ->select(
                's.id',
                's.shop_name',
                's.platform',
                DB::raw('COUNT(DISTINCT o.id) as order_count'),
                DB::raw('COALESCE(SUM(o.pay_amount), 0) as sales_amount'),
                DB::raw('COUNT(DISTINCT a.id) as refund_count'),
                DB::raw('COALESCE(SUM(a.refund_amount), 0) as refund_amount'),
                DB::raw('SUM(CASE WHEN o.ship_time IS NOT NULL AND o.order_created_at IS NOT NULL THEN 1 ELSE 0 END) as shipped_count'),
                DB::raw('SUM(CASE WHEN o.ship_time IS NOT NULL AND o.order_created_at IS NOT NULL AND DATEDIFF(o.ship_time, o.order_created_at) <= 1 THEN 1 ELSE 0 END) as on_time_count')
            )
            ->groupBy('s.id', 's.shop_name', 's.platform')
            ->orderByDesc('sales_amount')
            ->paginate(20);

        // 为每个店铺计算退款率和发货及时率
        $shops->getCollection()->transform(function ($shop) {
            // 退款率 = 退款单数 / 订单数 * 100%
            $shop->refund_rate = $shop->order_count > 0
                ? round($shop->refund_count / $shop->order_count * 100, 2)
                : 0;
            // 发货及时率 = 24小时内发货数 / 已发货数 * 100%
            $shop->ship_on_time_rate = $shop->shipped_count > 0
                ? round($shop->on_time_count / $shop->shipped_count * 100, 2)
                : 0;
            return $shop;
        });

        // 汇总统计
        $summary = [
            'total_shops'    => $shops->total(),
            'total_orders'   => $shops->sum('order_count'),
            'total_sales'    => $shops->sum('sales_amount'),
            'avg_refund_rate' => $shops->count() > 0
                ? round($shops->avg('refund_rate'), 2)
                : 0,
        ];

        return view('report.operation.shop-overview', compact('shops', 'summary', 'platformOptions'));
    }
}
