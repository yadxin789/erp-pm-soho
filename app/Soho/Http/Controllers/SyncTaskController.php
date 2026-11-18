<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\SyncTask;
use App\Soho\Services\PlatformServiceManager;
use Illuminate\Http\Request;

class SyncTaskController extends Controller
{
    /**
     * 同步任务列表
     */
    public function index(Request $request)
    {
        $tasks = SyncTask::with(['shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->task_type, function($query) use ($request) {
                $query->where('type', $request->task_type);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sync-task.index', compact('tasks'));
    }

    /**
     * 批量执行同步任务
     */
    public function batchExecute(Request $request)
    {
        $taskIds = $request->input('task_ids', []);
        if (empty($taskIds)) {
            return back()->with('error', '请选择要执行的任务');
        }

        $tasks = SyncTask::whereIn('id', $taskIds)->where('status', 'pending')->get();
        $count = 0;
        foreach ($tasks as $task) {
            $task->update(['status' => 'running', 'started_at' => now()]);
            $count++;
        }

        return back()->with('success', "已提交 {$count} 个任务执行");
    }

    /**
     * 查看同步任务详情
     */
    public function show(SyncTask $syncTask)
    {
        $syncTask->load(['shop']);
        return view('sync-task.show', compact('syncTask'));
    }

    /**
     * 执行同步任务
     */
    public function execute(Request $request, SyncTask $syncTask)
    {
        try {
            $syncTask->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // 根据任务类型执行同步
            $platformService = PlatformServiceManager::getService($syncTask->shop->platform);

            switch ($syncTask->type) {
                case 'order':
                    $result = $platformService->fetchOrders($syncTask->shop);
                    break;
                case 'stock':
                    $result = $platformService->syncStock($syncTask->shop);
                    break;
                case 'product':
                    $result = $platformService->fetchProducts($syncTask->shop);
                    break;
                default:
                    throw new \Exception('未知的同步任务类型');
            }

            $syncTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode($result),
            ]);

            return back()->with('success', '同步任务执行完成');

        } catch (\Exception $e) {
            $syncTask->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', '同步任务执行失败：' . $e->getMessage());
        }
    }
}
