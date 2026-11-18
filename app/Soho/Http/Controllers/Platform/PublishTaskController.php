<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品发布任务控制器
 * 负责管理批量发布任务，跟踪任务的整体进度与执行结果
 */
class PublishTaskController extends Controller
{
    /**
     * 发布任务列表
     * 展示当前租户下的批量发布任务，支持关键词搜索与状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询批量发布任务，限定当前租户
        $query = DB::table('publish_tasks')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按任务编号、任务名称、创建人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('task_no', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('creator', 'like', "%{$search}%");
            });
        }
        // 按目标平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按任务状态筛选
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
        $tasks = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的发布任务总数及各状态数量
        $baseQuery = DB::table('publish_tasks')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'running'    => (clone $baseQuery)->where('status', 'running')->count(),
            'completed'  => (clone $baseQuery)->where('status', 'completed')->count(),
            'failed'     => (clone $baseQuery)->where('status', 'failed')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'   => '待执行',
            'running'   => '执行中',
            'completed' => '已完成',
            'failed'    => '执行失败',
        ];

        return view('platform.publish-task.index', compact('tasks', 'stats', 'statusOptions'));
    }
}
