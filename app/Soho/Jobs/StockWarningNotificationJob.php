<?php

namespace App\Soho\Jobs;

use App\Soho\Models\GoodsWarnSetting;
use App\Soho\Models\SystemGoods;
use App\Soho\Models\User;
use App\Soho\Notifications\StockWarningNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 库存预警通知队列任务
 * 每日定时检测库存预警并发送通知
 */
class StockWarningNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     * @var int
     */
    public int $tries = 1;

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
        Log::info('开始执行库存预警检测任务');

        // 查询库存不足的商品
        $warningGoods = SystemGoods::where('status', 1)
            ->where('min_stock_warn', '>', 0)
            ->whereColumn('system_stock', '<=', 'min_stock_warn')
            ->get();

        if ($warningGoods->isEmpty()) {
            Log::info('库存预警检测：无需预警的商品');
            return;
        }

        Log::info('库存预警检测：发现 ' . $warningGoods->count() . ' 个商品库存不足');

        $warningData = [];

        foreach ($warningGoods as $goods) {
            $warnSetting = GoodsWarnSetting::where('system_goods_id', $goods->id)->first();

            // 检查预警开关是否开启
            if ($warnSetting && !$warnSetting->warn_enabled) {
                continue;
            }

            // 检查距离上次预警是否超过12小时（避免频繁通知）
            if ($warnSetting && $warnSetting->last_warn_at && $warnSetting->last_warn_at->diffInHours(now()) < 12) {
                continue;
            }

            // 收集预警数据
            $warningData[] = [
                'goods_id'      => $goods->id,
                'goods_name'    => $goods->goods_name,
                'system_sku'    => $goods->system_sku,
                'current_stock' => $goods->system_stock,
                'min_warn'      => $goods->min_stock_warn,
                'deficit'       => $goods->min_stock_warn - $goods->system_stock,
            ];

            // 更新预警设置（使用事务）
            DB::transaction(function () use ($warnSetting, $goods) {
                if ($warnSetting) {
                    $warnSetting->update(['last_warn_at' => now()]);
                } else {
                    GoodsWarnSetting::create([
                        'system_goods_id'     => $goods->id,
                        'min_stock_threshold' => $goods->min_stock_warn,
                        'warn_enabled'        => true,
                        'notify_users'        => [],
                        'last_warn_at'        => now(),
                    ]);
                }
            });
        }

        // 如果有需要预警的商品，发送通知
        if (!empty($warningData)) {
            $this->sendWarningNotification($warningData);
        }

        Log::info('库存预警检测任务完成', ['warning_count' => count($warningData)]);
    }

    /**
     * 发送库存预警通知
     *
     * @param array $warningData 预警商品数据
     * @return void
     */
    protected function sendWarningNotification(array $warningData): void
    {
        // 发送给所有超级管理员和仓储管理员
        $admins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super_admin', 'warehouse_manager']);
        })->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new StockWarningNotification($warningData));
            Log::info('库存预警通知已发送', [
                'admin_count' => $admins->count(),
                'goods_count' => count($warningData),
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
        Log::error('库存预警检测任务失败', [
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * 获取任务唯一标识
     *
     * @return string
     */
    public function getJobId(): string
    {
        return 'stock_warning_' . now()->format('Ymd');
    }
}