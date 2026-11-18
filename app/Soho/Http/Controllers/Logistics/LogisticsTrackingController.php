<?php

namespace App\Soho\Http\Controllers\Logistics;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 物流轨迹控制器
 * 用于查询包裹物流轨迹信息，支持按运单号、快递公司、轨迹状态筛选
 */
class LogisticsTrackingController extends Controller
{
    /**
     * 物流轨迹列表
     * 支持按运单号、快递公司、轨迹状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建物流轨迹查询
        $query = DB::table('soho_logistics_trackings')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键字搜索：运单号、跟踪号、订单号
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('waybill_no', 'like', "%{$search}%")
                  ->orWhere('tracking_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%");
            });
        }
        // 快递公司筛选
        if ($expressCompany = $request->input('soho_express_company')) {
            $query->where('soho_express_company', $expressCompany);
        }
        // 轨迹状态筛选
        if ($status = $request->input('status')) {
            $query->where('tracking_status', $status);
        }
        // 轨迹更新起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('updated_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 轨迹更新结束日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('updated_at', '<=', $dateTo . ' 23:59:59');
        }

        // 分页查询物流轨迹列表
        $trackings = $query->orderByDesc('updated_at')->paginate(20);

        // 统计各轨迹状态数量
        $stats = [
            // 总包裹数
            'total' => DB::table('soho_logistics_trackings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            // 运输中包裹数
            'in_transit' => DB::table('soho_logistics_trackings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('tracking_status', 'in_transit')->count(),
            // 已签收包裹数
            'delivered' => DB::table('soho_logistics_trackings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('tracking_status', 'delivered')->count(),
            // 异常件数
            'exception' => DB::table('soho_logistics_trackings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('tracking_status', 'exception')->count(),
        ];

        // 轨迹状态选项
        $statusOptions = ['in_transit' => '运输中', 'delivered' => '已签收', 'exception' => '异常', 'pending' => '待发货'];
        // 快递公司选项
        $expressOptions = ['SF' => '顺丰速运', 'YTO' => '圆通速递', 'ZTO' => '中通快递', 'STO' => '申通快递', 'YD' => '韵达快递', 'JD' => '京东物流'];

        return view('logistics.trackings.index', compact('trackings', 'stats', 'statusOptions', 'expressOptions'));
    }
}
