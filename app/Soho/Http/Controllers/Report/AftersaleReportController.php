<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 售后报表控制器
 * 展示售后统计：退款金额、退货数量、换货数量、争议数，支持时间范围筛选
 */
class AftersaleReportController extends Controller
{
    /**
     * 售后报表首页
     * 汇总当前租户的售后数据，支持时间范围、店铺、类型筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 店铺筛选
        $shopId = $request->input('shop_id');

        // 类型筛选
        $type = $request->input('type');

        // 售后类型选项
        $typeOptions = [
            'refund'     => '仅退款',
            'return'     => '退货退款',
            'exchange'   => '换货',
            'reship'     => '补发',
            'dispute'    => '争议',
        ];

        // 获取当前租户下的店铺列表
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 售后单基础查询
        $query = DB::table('soho_order_after_sales')
            ->where('tenant_id', $tenantId)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->whereBetween('apply_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // 分页数据
        $aftersales = (clone $query)
            ->orderByDesc('apply_time')
            ->paginate(20);

        // 统计卡片数据：退款金额、退货数量、换货数量、争议数
        $stats = [
            // 退款总金额
            'refund_amount' => (clone $query)->sum('refund_amount'),
            // 退货数量
            'return_count'  => (clone $query)->where('type', 'return')->count(),
            // 换货数量
            'exchange_count' => (clone $query)->where('type', 'exchange')->count(),
            // 争议数量
            'dispute_count' => (clone $query)->where('type', 'dispute')->count(),
            // 售后单总数
            'total_count'   => (clone $query)->count(),
        ];

        // 按类型分组的统计数据（用于图表展示）
        $typeStats = (clone $query)
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(refund_amount) as amount'))
            ->groupBy('type')
            ->get();

        return view('report.operation.aftersale', compact('aftersales', 'stats', 'typeStats', 'shops', 'typeOptions', 'startDate', 'endDate'));
    }
}
