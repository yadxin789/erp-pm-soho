<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 售后明细报表控制器
 * 展示售后单明细：售后单号、订单号、类型、金额、处理时长
 */
class AftersaleDetailController extends Controller
{
    /**
     * 售后明细列表
     * 支持按售后类型、状态、时间范围筛选，计算每单的处理时长
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 售后类型筛选
        $type = $request->input('type');
        // 状态筛选
        $status = $request->input('status');
        // 时间范围筛选（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));
        // 店铺筛选
        $shopId = $request->input('shop_id');

        // 类型选项
        $typeOptions = [
            'refund'   => '仅退款',
            'return'   => '退货退款',
            'exchange' => '换货',
            'reship'   => '补发',
            'dispute'  => '争议',
        ];

        // 状态选项
        $statusOptions = [
            'pending'    => '待处理',
            'processing' => '处理中',
            'approved'   => '已同意',
            'rejected'   => '已拒绝',
            'completed'  => '已完成',
            'cancelled'  => '已取消',
        ];

        // 获取当前租户下的店铺列表
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 查询售后明细（关联订单与店铺信息）
        $items = DB::table('soho_order_after_sales as a')
            ->leftJoin('soho_platform_orders as o', 'a.platform_order_id', '=', 'o.id')
            ->leftJoin('soho_shops as s', 'a.shop_id', '=', 's.id')
            ->where('a.tenant_id', $tenantId)
            ->when($type, fn($q) => $q->where('a.type', $type))
            ->when($status, fn($q) => $q->where('a.status', $status))
            ->when($shopId, fn($q) => $q->where('a.shop_id', $shopId))
            ->whereBetween('a.apply_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'a.id',
                'a.after_sale_no',
                'o.platform_order_no',
                'a.type',
                'a.status',
                'a.refund_amount',
                'a.reason',
                's.shop_name',
                'a.apply_time',
                'a.handle_time'
            )
            ->orderByDesc('a.apply_time')
            ->paginate(20);

        // 计算每单的处理时长（小时）
        $items->getCollection()->transform(function ($item) {
            if ($item->apply_time && $item->handle_time) {
                // SQLite 使用 julianday 计算时间差
                $item->handle_hours = round(
                    (strtotime($item->handle_time) - strtotime($item->apply_time)) / 3600,
                    2
                );
            } else {
                $item->handle_hours = null;
            }
            return $item;
        });

        // 汇总统计
        $baseQuery = DB::table('soho_order_after_sales')
            ->where('tenant_id', $tenantId)
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->whereBetween('apply_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $stats = [
            'total_count'    => (clone $baseQuery)->count(),
            'total_amount'   => (clone $baseQuery)->sum('refund_amount'),
            'pending_count'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'completed_count' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];

        return view('report.data.aftersale-detail', compact('items', 'stats', 'shops', 'typeOptions', 'statusOptions', 'startDate', 'endDate'));
    }
}
