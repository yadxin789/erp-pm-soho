<?php

namespace App\Soho\Jobs;

use App\Soho\Models\PlatformSku;
use App\Soho\Models\StockSyncLog;
use App\Soho\Models\SystemGoods;
use App\Soho\Models\User;
use App\Soho\Notifications\SyncFailedNotification;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 单商品库存同步队列任务
 * 将单个商品库存同步至所有关联平台
 */
class SyncSingleGoodsStockJob implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     * @var int
     */
    public int $tries = 3;

    /**
     * 重试间隔（秒）
     * @var int|array
     */
    public int|array $backoff = [30, 60, 120];

    /**
     * 任务超时时间（秒）
     * @var int
     */
    public int $timeout = 120;

    /**
     * 商品ID
     * @var int
     */
    protected int $goodsId;

    /**
     * 创建任务实例
     *
     * @param SystemGoods $goods 系统商品
     */
    public function __construct(
        protected SystemGoods $goods,
    ) {
        $this->goodsId = $goods->id;
        $this->onQueue('stock-sync');
    }

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle(): void
    {
        // 重新获取商品数据
        $goods = SystemGoods::find($this->goodsId);
        if (!$goods) {
            Log::warning("商品不存在", ['goods_id' => $this->goodsId]);
            return;
        }

        $platformSkus = PlatformSku::where('system_goods_id', $goods->id)
            ->with('shop')
            ->get();

        if ($platformSkus->isEmpty()) {
            Log::info("商品 {$goods->id} 无平台SKU映射，跳过库存同步");
            return;
        }

        $success = 0;
        $failed = 0;

        foreach ($platformSkus as $platformSku) {
            $shop = $platformSku->shop;
            if (!$shop || $shop->auth_status !== 1) {
                continue;
            }

            try {
                $service = PlatformServiceFactory::create($shop);
                $result = $service->syncStock([
                    [
                        'system_goods_id'   => $goods->id,
                        'platform_goods_id' => $platformSku->platform_goods_id,
                        'quantity'          => $goods->system_stock,
                        'sku'               => $platformSku->platform_sku,
                    ],
                ]);

                if ($result['success']) {
                    foreach ($result['results'] as $item) {
                        // 使用事务处理同步成功逻辑
                        DB::transaction(function () use ($platformSku, $goods, $item, $shop, &$success, &$failed) {
                            if ($item['success']) {
                                // 同步成功后更新平台SKU库存
                                $platformSku->update([
                                    'platform_stock' => $goods->system_stock,
                                    'last_sync_at'   => now(),
                                ]);

                                // 记录同步日志
                                StockSyncLog::create([
                                    'system_goods_id' => $goods->id,
                                    'shop_id'         => $shop->id,
                                    'platform'        => $shop->platform,
                                    'before_stock'    => $platformSku->platform_stock,
                                    'after_stock'     => $goods->system_stock,
                                    'sync_type'       => 'push',
                                    'sync_status'     => 'success',
                                ]);

                                $success++;
                            } else {
                                // 记录失败日志
                                StockSyncLog::create([
                                    'system_goods_id' => $goods->id,
                                    'shop_id'         => $shop->id,
                                    'platform'        => $shop->platform,
                                    'before_stock'    => $platformSku->platform_stock,
                                    'after_stock'     => $goods->system_stock,
                                    'sync_type'       => 'push',
                                    'sync_status'     => 'failed',
                                    'error_message'   => $item['error'] ?? 'Unknown',
                                ]);

                                $failed++;
                                Log::error("库存同步失败", [
                                    'goods_id'  => $goods->id,
                                    'shop_id'   => $shop->id,
                                    'shop_name' => $shop->shop_name,
                                    'error'     => $item['error'] ?? 'Unknown',
                                ]);
                            }
                        });
                    }
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("库存同步异常: 商品 {$goods->id}, 店铺 {$shop->shop_name}, " . $e->getMessage());
            }
        }

        Log::info("单商品库存同步完成", [
            'goods_id'   => $goods->id,
            'goods_name' => $goods->goods_name,
            'success'    => $success,
            'failed'     => $failed,
        ]);
    }

    /**
     * 任务失败处理
     *
     * @param \Throwable $exception 异常信息
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('单商品库存同步任务最终失败', [
            'goods_id' => $this->goodsId,
            'error' => $exception->getMessage(),
        ]);

        // 发送失败通知给超级管理员
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            $goods = SystemGoods::find($this->goodsId);
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'stock_sync',
                'task_name' => '单商品库存同步',
                'shop_id' => null,
                'shop_name' => '全部店铺',
                'error_message' => "商品[{$goods?->goods_name}]库存同步失败: " . $exception->getMessage(),
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
        return "single_stock_sync_{$this->goodsId}";
    }
}