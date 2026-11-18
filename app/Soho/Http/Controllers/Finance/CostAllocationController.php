<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 费用分摊控制器
 * 用于管理费用分摊单，支持按分摊单号、费用类型、分摊对象、状态筛选
 */
class CostAllocationController extends Controller
{
    /**
     * 费用分摊列表
     * 支持按分摊单号、费用类型、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建费用分摊查询，限定当前租户
        $query = DB::table('cost_allocations')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按分摊单号、分摊对象模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('allocation_no', 'like', "%{$search}%")
                  ->orWhere('target', 'like', "%{$search}%");
            });
        }
        // 按费用类型筛选
        if ($costType = $request->input('cost_type')) {
            $query->where('cost_type', $costType);
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
        $allocations = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的分摊单总数及各状态数量、总金额
        $baseQuery = DB::table('cost_allocations')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            // 分摊单总数
            'total'        => (clone $baseQuery)->count(),
            // 待审核
            'pending'      => (clone $baseQuery)->where('status', 'pending')->count(),
            // 已分摊
            'allocated'    => (clone $baseQuery)->where('status', 'allocated')->count(),
            // 已驳回
            'rejected'     => (clone $baseQuery)->where('status', 'rejected')->count(),
            // 分摊总金额
            'total_amount' => (clone $baseQuery)->sum('amount'),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'   => '待审核',
            'allocated' => '已分摊',
            'rejected'  => '已驳回',
        ];

        // 费用类型选项
        $costTypeOptions = [
            'logistics'   => '物流费用',
            'platform'    => '平台佣金',
            'storage'     => '仓储费用',
            'packaging'   => '包装费用',
            'other'       => '其他费用',
        ];

        return view('finance.cost-allocation.index', compact('allocations', 'stats', 'statusOptions', 'costTypeOptions'));
    }
}
