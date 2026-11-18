<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * 队列监控控制器
 * 监控队列任务执行状态、失败任务重试、定时任务列表
 * 通过数据库查询获取队列状态，通过 Artisan 命令管理任务
 */
class QueueMonitorController extends Controller
{
    /**
     * 显示队列监控概览
     */
    public function index()
    {
        // 队列概览统计
        $stats = $this->getQueueStats();
        // 失败任务列表
        $failedJobs = $this->getFailedJobs();
        // 定时任务列表
        $scheduledTasks = $this->getScheduledTasks();
        // 队列连接信息
        $queueConfig = $this->getQueueConfig();

        return view('system.queue.index', compact('stats', 'failedJobs', 'scheduledTasks', 'queueConfig'));
    }

    /**
     * 重试失败任务
     */
    public function retry($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => $id]);
            return back()->with('success', "失败任务 #{$id} 已重新加入队列");
        } catch (\Exception $e) {
            return back()->with('error', "重试失败：{$e->getMessage()}");
        }
    }

    /**
     * 删除失败任务
     */
    public function forget($id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            return back()->with('success', "失败任务 #{$id} 已删除");
        } catch (\Exception $e) {
            return back()->with('error', "删除失败：{$e->getMessage()}");
        }
    }

    /**
     * 清空所有失败任务
     */
    public function flush()
    {
        try {
            Artisan::call('queue:flush');
            return back()->with('success', '所有失败任务已清空');
        } catch (\Exception $e) {
            return back()->with('error', "清空失败：{$e->getMessage()}");
        }
    }

    /**
     * 获取队列统计信息
     */
    private function getQueueStats(): array
    {
        // 待处理任务数
        $pendingCount = 0;
        $failedCount = 0;
        $delayedCount = 0;

        try {
            // jobs 表统计
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $pendingCount = DB::table('jobs')->count();
                // 延迟任务（reserved_at 不为空或 available_at 大于当前时间）
                $delayedCount = DB::table('jobs')
                    ->where('available_at', '>', time())
                    ->count();
            }
        } catch (\Exception $e) {
            // 查询失败保持 0
        }

        try {
            // failed_jobs 表统计
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedCount = DB::table('failed_jobs')->count();
            }
        } catch (\Exception $e) {
            // 查询失败保持 0
        }

        return [
            'pending' => $pendingCount,
            'failed' => $failedCount,
            'delayed' => $delayedCount,
            'total' => $pendingCount + $failedCount,
        ];
    }

    /**
     * 获取失败任务列表
     */
    private function getFailedJobs()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                return collect([]);
            }

            return DB::table('failed_jobs')
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->map(function ($job) {
                    // 解析异常信息，截取首行作为摘要
                    $exception = $job->exception ?? '';
                    $job->exception_short = mb_substr($exception, 0, 200);
                    $job->payload = json_decode($job->payload ?? '{}', true);
                    return $job;
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 获取定时任务列表（通过 schedule:list 命令）
     */
    private function getScheduledTasks(): array
    {
        try {
            Artisan::call('schedule:list');
            $output = Artisan::output();

            // 解析 schedule:list 输出
            $tasks = [];
            $lines = explode("\n", $output);

            foreach ($lines as $line) {
                $line = trim($line);
                // 跳过空行和标题行
                if (empty($line) || str_starts_with($line, '|') === false || str_contains($line, 'Cron') || str_contains($line, 'Command')) {
                    continue;
                }
                // 解析表格行
                $parts = array_map('trim', array_filter(explode('|', $line)));
                if (count($parts) >= 2) {
                    $tasks[] = [
                        'expression' => $parts[0] ?? '-',
                        'command' => $parts[1] ?? '-',
                        'next_run' => $parts[2] ?? '-',
                    ];
                }
            }

            return $tasks;
        } catch (\Exception $e) {
            // 命令执行失败返回空数组
            return [];
        }
    }

    /**
     * 获取队列配置信息
     */
    private function getQueueConfig(): array
    {
        $defaultConnection = config('queue.default');
        $connectionConfig = config("queue.connections.{$defaultConnection}");

        return [
            'default' => $defaultConnection,
            'driver' => $connectionConfig['driver'] ?? '-',
            'queue' => $connectionConfig['queue'] ?? 'default',
            'retry_after' => $connectionConfig['retry_after'] ?? 90,
            'block_for' => $connectionConfig['block_for'] ?? null,
        ];
    }
}
