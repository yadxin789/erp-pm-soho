<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 售后争议控制器
 * 负责处理售后争议单的管理与跟踪，支持按类型、级别、状态多维度筛选
 */
class DisputeController extends Controller
{
    /**
     * 售后争议列表
     * 展示当前租户下的售后争议单，支持按争议单号、类型、级别、状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询售后争议单，限定当前租户
        $query = DB::table('aftersale_disputes')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按争议单号、订单号、客户名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('dispute_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        // 按争议类型筛选
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        // 按争议级别筛选
        if ($level = $request->input('level')) {
            $query->where('level', $level);
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
        $disputes = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的争议单总数及各状态数量
        $baseQuery = DB::table('aftersale_disputes')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'pending'    => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'resolved'   => (clone $baseQuery)->where('status', 'resolved')->count(),
            'escalated'  => (clone $baseQuery)->where('status', 'escalated')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'    => '待处理',
            'processing' => '处理中',
            'resolved'   => '已解决',
            'escalated'  => '已升级',
        ];

        // 级别筛选选项
        $levelOptions = [
            'low'      => '低',
            'medium'   => '中',
            'high'     => '高',
            'critical' => '紧急',
        ];

        // 类型筛选选项（常见售后争议类型）
        $typeOptions = [
            'quality'    => '质量问题',
            'logistics'  => '物流问题',
            'service'    => '服务问题',
            'price'      => '价格争议',
            'other'      => '其他',
        ];

        return view('aftersale.disputes.index', compact('disputes', 'stats', 'statusOptions', 'levelOptions', 'typeOptions'));
    }
}
