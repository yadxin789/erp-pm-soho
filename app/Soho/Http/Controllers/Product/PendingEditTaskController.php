<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 待编辑任务控制器
 * 用于管理待编辑商品任务的列表展示与筛选
 */
class PendingEditTaskController extends Controller
{
    /**
     * 待编辑任务列表
     * 支持按商品名称、优先级、截止日期筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 待编辑任务列表查询：使用 when 条件查询实现动态筛选
        $tasks = DB::table('product_edit_tasks')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->where('status', 'pending')
            ->when($search = $request->input('search'), function ($query) use ($search) {
                // 关键字搜索：商品名称、任务编号
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('task_no', 'like', "%{$search}%");
                });
            })
            ->when($priority = $request->input('priority'), function ($query) use ($priority) {
                $query->where('priority', $priority);
            })
            ->when($assigneeId = $request->input('assignee_id'), function ($query) use ($assigneeId) {
                $query->where('assignee_id', $assigneeId);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('deadline', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('deadline', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // 优先级字典
        $priorityOptions = [
            'high' => '高',
            'medium' => '中',
            'low' => '低',
        ];

        // 统计卡片数据
        $stats = [
            'total' => DB::table('product_edit_tasks')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'pending')->count(),
            'high' => DB::table('product_edit_tasks')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'pending')->where('priority', 'high')->count(),
            'overdue' => DB::table('product_edit_tasks')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'pending')->where('deadline', '<', now())->count(),
        ];

        return view('product.tasks.pending-edit.index', compact('tasks', 'priorityOptions', 'stats'));
    }
}
