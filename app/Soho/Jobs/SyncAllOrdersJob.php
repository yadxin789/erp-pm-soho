<?php

namespace App\Soho\Jobs;

use App\Soho\Models\User;
use App\Soho\Notifications\SyncFailedNotification;
use App\Soho\Services\OrderSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 全平台订单同步队列任务
 * 定时抓取所有电商平台订单
 */
class SyncAllOrdersJob implements ShouldQueue
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
        $this->onQueue('order-sync');
    }

    /**
     * 执行任务
     *
     * @param OrderSyncService $service 订单同步服务
     * @return void
     */
    public function handle(OrderSyncService $service): void
    {
        Log::info('开始全平台订单同步任务');

        try {
            // syncAll 内部已按店铺+订单粒度处理事务，无需外层包裹长事务
            $results = $service->syncAll();

            $totalOrders = 0;
            $totalSuccess = 0;
            $totalFailed = 0;

            foreach ($results as $result) {
                $totalOrders += ($result['saved'] ?? 0);
                if ($result['success']) {
                    $totalSuccess += $result['saved'] ?? 0;
                } else {
                    $totalFailed += $result['saved'] ?? 0;
                }
            }

            Log::info("订单同步完成", [
                'total_orders' => $totalOrders,
                'success' => $totalSuccess,
                'failed' => $totalFailed,
            ]);

            if ($totalFailed > 0) {
                $this->sendPartialFailureNotification($totalFailed, $totalOrders);
            }
        } catch (\Exception $e) {
            Log::error('订单同步任务失败: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * 发送部分失败通知
     *
     * @param int $failedCount 失败数量
     * @param int $totalCount 总数量
     * @return void
     */
    protected function sendPartialFailureNotification(int $failedCount, int $totalCount): void
    {
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'order_sync',
                'task_name' => '订单同步',
                'shop_id' => null,
                'shop_name' => '全部店铺',
                'error_message' => "部分订单同步失败，失败数量：{$failedCount}/{$totalCount}",
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
        Log::error('订单同步任务最终失败', [
            'error' => $exception->getMessage(),
        ]);

        // 发送失败通知给超级管理员
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'order_sync',
                'task_name' => '订单同步',
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
        return 'order_sync_' . now()->format('YmdHi');
    }
}