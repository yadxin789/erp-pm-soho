<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 对账任务控制器
 * 用于管理对账任务，支持按任务号、平台、对账周期、状态筛选
 */
class ReconcileTaskController extends Controller
{
    /**
     * 对账任务列表
     * 支持按任务号、平台、状态、对账周期筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建对账任务查询，限定当前租户
        $query = DB::table('reconcile_tasks')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按任务号、平台模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('task_no', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%");
            });
        }
        // 按平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按对账状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按对账周期起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('period_start', '>=', $dateFrom . ' 00:00:00');
        }
        // 按对账周期截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('period_end', '<=', $dateTo . ' 23:59:59');
        }

        // 按创建时间倒序分页，每页20条
        $tasks = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的对账任务总数及各状态数量、差异金额
        $baseQuery = DB::table('reconcile_tasks')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            // 任务总数
            'total'         => (clone $baseQuery)->count(),
            // 待对账
            'pending'       => (clone $baseQuery)->where('status', 'pending')->count(),
            // 已对账
            'reconciled'    => (clone $baseQuery)->where('status', 'reconciled')->count(),
            // 有差异
            'disputed'      => (clone $baseQuery)->where('status', 'disputed')->count(),
            // 差异总金额
            'total_diff'    => (clone $baseQuery)->sum('diff_amount'),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'    => '待对账',
            'reconciled' => '已对账',
            'disputed'   => '有差异',
            'failed'     => '对账失败',
        ];

        // 平台选项
        $platformOptions = [
            'taobao'   => '淘宝',
            'tmall'    => '天猫',
            'jd'       => '京东',
            'pdd'      => '拼多多',
            'douyin'   => '抖音',
            'kuaishou' => '快手',
        ];

        return view('finance.reconcile-task.index', compact('tasks', 'stats', 'statusOptions', 'platformOptions'));
    }
}
