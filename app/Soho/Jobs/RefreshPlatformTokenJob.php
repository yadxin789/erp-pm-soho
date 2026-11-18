<?php

namespace App\Soho\Jobs;

use App\Soho\Models\Shop;
use App\Soho\Models\SyncTaskLog;
use App\Soho\Models\User;
use App\Soho\Notifications\TokenExpiredNotification;
use App\Soho\Notifications\SyncFailedNotification;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 平台Token刷新队列任务
 * 定时刷新各电商平台授权令牌
 */
class RefreshPlatformTokenJob implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     * @var int
     */
    public int $tries = 2;

    /**
     * 任务超时时间（秒）
     * @var int
     */
    public int $timeout = 120;

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('开始Token自动刷新任务');

        // 查询24小时内即将过期的店铺Token
        $shops = Shop::where('status', 1)
            ->where('auth_status', 1)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<', now()->addHours(24))
            ->get();

        foreach ($shops as $shop) {
            try {
                $service = PlatformServiceFactory::create($shop);
                $result = $service->refreshAccessToken();

                // 使用事务记录日志
                DB::transaction(function () use ($shop, $result) {
                    SyncTaskLog::create([
                        'task_name'  => "Token刷新 - {$shop->platform_label} {$shop->shop_name}",
                        'task_type'  => 'token_refresh',
                        'platform'   => $shop->platform,
                        'shop_id'    => $shop->id,
                        'status'     => $result ? 'success' : 'failed',
                        'started_at' => now(),
                        'finished_at'=> now(),
                    ]);
                });

                Log::info("Token刷新完成: {$shop->shop_name} - " . ($result ? '成功' : '失败'));

                // 如果刷新失败，发送Token过期通知
                if (!$result) {
                    $this->sendTokenExpiredNotification($shop, false);
                }
            } catch (\Exception $e) {
                // 使用事务记录失败日志
                DB::transaction(function () use ($shop, $e) {
                    SyncTaskLog::create([
                        'task_name'     => "Token刷新 - {$shop->platform_label} {$shop->shop_name}",
                        'task_type'     => 'token_refresh',
                        'platform'      => $shop->platform,
                        'shop_id'       => $shop->id,
                        'status'        => 'failed',
                        'error_message' => $e->getMessage(),
                        'started_at'    => now(),
                        'finished_at'   => now(),
                    ]);
                });

                Log::error("Token刷新失败: {$shop->shop_name} - " . $e->getMessage());

                // 发送Token过期通知
                $this->sendTokenExpiredNotification($shop, true);
            }
        }

        // 检查已过期的Token，发送紧急通知
        $expiredShops = Shop::where('status', 1)
            ->where('auth_status', 1)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<', now())
            ->get();

        foreach ($expiredShops as $shop) {
            $this->sendTokenExpiredNotification($shop, true);
        }

        Log::info('Token自动刷新任务完成');
    }

    /**
     * 发送Token过期通知
     *
     * @param Shop $shop 店铺信息
     * @param bool $isExpired 是否已过期
     * @return void
     */
    protected function sendTokenExpiredNotification(Shop $shop, bool $isExpired): void
    {
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new TokenExpiredNotification([
                'id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'platform' => $shop->platform,
                'platform_label' => $shop->platform_label,
                'token_expires_at' => $shop->token_expires_at?->toDateTimeString() ?? '未知',
            ], $isExpired));

            Log::info("Token过期通知已发送", [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'is_expired' => $isExpired,
            ]);
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
        Log::error('Token自动刷新任务最终失败', [
            'error' => $exception->getMessage(),
        ]);

        // 发送失败通知给超级管理员
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'token_refresh',
                'task_name' => 'Token自动刷新',
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
        return 'token_refresh_' . now()->format('YmdH');
    }
}