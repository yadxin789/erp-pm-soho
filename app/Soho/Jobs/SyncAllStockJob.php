<?php

namespace App\Soho\Jobs;

use App\Soho\Models\User;
use App\Soho\Notifications\SyncFailedNotification;
use App\Soho\Services\StockSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 全平台库存同步队列任务
 * 定时同步所有电商平台库存
 */
class SyncAllStockJob implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     * @var int
     */
    public int $tries = 3;

    /**
     * 重试间隔（秒）
     * @var int
     */
    public int $backoff = 60;

    /**
     * 任务超时时间（秒）
     * @var int
     */
    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('stock-sync');
    }

    /**
     * 执行任务
     *
     * @param StockSyncService $service 库存同步服务
     * @return void
     */
    public function handle(StockSyncService $service): void
    {
        Log::info('开始全平台库存同步任务');

        try {
            // syncAll 内部已按店铺粒度处理事务，无需外层包裹长事务
            $results = $service->syncAll();

            $totalSync = 0;
            $totalFailed = 0;

            foreach ($results as $result) {
                if ($result['success']) {
                    $totalSync += $result['synced'] ?? 0;
                    $totalFailed += $result['failed'] ?? 0;
                }
            }

            Log::info("库存同步完成", [
                'synced' => $totalSync,
                'failed' => $totalFailed,
            ]);

            if ($totalFailed > 0) {
                $this->sendPartialFailureNotification($totalFailed, $totalSync);
            }
        } catch (\Exception $e) {
            Log::error('库存同步任务失败: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * 发送部分失败通知
     *
     * @param int $failedCount 失败数量
     * @param int $syncCount 同步成功数量
     * @return void
     */
    protected function sendPartialFailureNotification(int $failedCount, int $syncCount): void
    {
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'stock_sync',
                'task_name' => '库存同步',
                'shop_id' => null,
                'shop_name' => '全部店铺',
                'error_message' => "部分库存同步失败，失败数量：{$failedCount}/{$syncCount}",
                'attempts' => 1,
                'failed_at' => now()->toDateTimeString(),
            ]));
        }
    }

    /**
     * 任务失败处理
     *
     * @param \Throwable $exception 异常信息
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('库存同步任务最终失败', [
            'error' => $exception->getMessage(),
        ]);

        // 发送失败通知给超级管理员
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'stock_sync',
                'task_name' => '库存同步',
                'shop_id' => null,
                'shop_name' => '全部店铺',
                'error_message' => $exception->getMessage(),
                'attempts' => $this->tries,
                'failed_at' => now()->toDateTimeString(),
            ]));
        }
    }

    /**
     * 获取任务唯一标识
     *
     * @return string
     */
    public function getJobId(): string
    {
        return 'stock_sync_' . now()->format('YmdHi');
    }
}