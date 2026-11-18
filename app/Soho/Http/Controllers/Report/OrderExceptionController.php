<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 订单异常报表控制器
 * 展示订单异常信息：订单号、异常类型、异常描述、发生时间，支持按异常类型筛选
 */
class OrderExceptionController extends Controller
{
    /**
     * 订单异常列表
     * 展示当前租户的异常订单，支持按异常类型、时间范围、店铺筛选
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 异常类型筛选参数
        $type = $request->input('type');

        // 时间范围筛选参数（默认近30天）
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 店铺筛选参数
        $shopId = $request->input('shop_id');

        // 异常类型选项
        $typeOptions = [
            'address'      => '地址异常',
            'payment'      => '支付异常',
            'stock'        => '库存不足',
            'logistics'    => '物流异常',
            'risk'         => '风险订单',
            'manual'       => '人工标记',
        ];

        // 获取当前租户下的店铺列表
        $shops = DB::table('soho_shops')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->orderBy('shop_name')
            ->get();

        // 查询异常订单（关联店铺信息）
        $query = DB::table('soho_platform_orders as o')
            ->leftJoin('soho_shops as s', 'o.shop_id', '=', 's.id')
            ->where('o.tenant_id', $tenantId)
            ->where('o.is_abnormal', 1)
            ->when($type, fn($q) => $q->where('o.abnormal_reason', 'like', "%{$type}%"))
            ->when($shopId, fn($q) => $q->where('o.shop_id', $shopId))
            ->whereBetween('o.order_created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'o.id',
                'o.platform_order_no',
                'o.platform',
                's.shop_name',
                'o.order_status',
                'o.abnormal_reason',
                'o.total_amount',
                'o.order_created_at',
                'o.updated_at'
            );

        $items = (clone $query)->orderByDesc('o.order_created_at')->paginate(20);

        // 统计卡片：异常订单总数及各类型异常数量
        $baseQuery = DB::table('soho_platform_orders')
            ->where('tenant_id', $tenantId)
            ->where('is_abnormal', 1);
        $stats = [
            'total'         => (clone $baseQuery)->count(),
            'address'       => (clone $baseQuery)->where('abnormal_reason', 'like', '%地址%')->count(),
            'payment'       => (clone $baseQuery)->where('abnormal_reason', 'like', '%支付%')->count(),
            'stock'         => (clone $baseQuery)->where('abnormal_reason', 'like', '%库存%')->count(),
            'logistics'     => (clone $baseQuery)->where('abnormal_reason', 'like', '%物流%')->count(),
        ];

        return view('report.operation.order-exception', compact('items', 'stats', 'shops', 'typeOptions', 'startDate', 'endDate'));
    }
}
