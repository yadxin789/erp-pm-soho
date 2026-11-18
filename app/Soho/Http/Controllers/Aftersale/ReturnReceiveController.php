<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 退货收货控制器
 * 负责处理退货物流的收货流程，跟踪退货单从待发货到已签收的全状态流转
 */
class ReturnReceiveController extends Controller
{
    /**
     * 退货收货列表
     * 展示当前租户下的退货单，支持按退货单号、订单号、状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询退货收货单，限定当前租户
        $query = DB::table('return_orders')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按退货单号、订单号模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%");
            });
        }
        // 按状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按创建时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按创建时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按创建时间倒序分页，每页20条
        $returns = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的退货单总数及各状态数量
        $baseQuery = DB::table('return_orders')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'pending'    => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_transit' => (clone $baseQuery)->where('status', 'in_transit')->count(),
            'received'   => (clone $baseQuery)->where('status', 'received')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'    => '待发货',
            'in_transit' => '运输中',
            'received'   => '已签收',
            'rejected'   => '拒收',
        ];

        return view('aftersale.return-receives.index', compact('returns', 'stats', 'statusOptions'));
    }
}
