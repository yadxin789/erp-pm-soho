<?php

namespace App\Soho\Jobs;

use App\Soho\Models\GoodsPublishRecord;
use App\Soho\Models\PlatformSku;
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
 * 商品推送队列任务
 * 将系统商品推送至电商平台
 */
class PushGoodsJob implements ShouldQueue
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
    public int|array $backoff = [60, 120, 300];

    /**
     * 任务超时时间（秒）
     * @var int
     */
    public int $timeout = 120;

    /**
     * 店铺平台标识
     * @var string
     */
    protected string $shopPlatform = 'taobao';

    /**
     * 发布记录ID
     * @var int|null
     */
    protected ?int $publishRecordId = null;

    /**
     * 创建任务实例
     *
     * @param SystemGoods $goods 系统商品
     * @param int $shopId 店铺ID
     */
    public function __construct(
        protected SystemGoods $goods,
        protected int $shopId,
    ) {
        $shop = \App\Soho\Models\Shop::find($this->shopId);
        $this->shopPlatform = $shop?->platform ?? 'taobao';
    }

    /**
     * 获取任务中间件
     *
     * @return array
     */
    public function middleware(): array
    {
        return [\App\Soho\Middleware\RateLimitedMiddleware::forPlatform($this->shopPlatform)];
    }

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info("开始推送商品", [
            'goods_id' => $this->goods->id,
            'goods_name' => $this->goods->goods_name,
            'shop_id' => $this->shopId,
        ]);

        $platformSku = PlatformSku::where('system_goods_id', $this->goods->id)
            ->where('shop_id', $this->shopId)
            ->first();

        // 商品未绑定SKU映射，记录警告并退出
        if (!$platformSku) {
            Log::warning("商品 {$this->goods->id} 在店铺 {$this->shopId} 未绑定SKU映射");
            return;
        }

        $shop = $platformSku->shop;

        // 使用数据库事务确保数据一致性
        $publishRecord = DB::transaction(function () use ($platformSku, $shop) {
            return GoodsPublishRecord::create([
                'system_goods_id' => $this->goods->id,
                'shop_id'         => $this->shopId,
                'platform'        => $shop->platform,
                'publish_status'  => 'publishing',
                'operator_id'     => auth()->id(),
            ]);
        });

        $this->publishRecordId = $publishRecord->id;

        try {
            $service = PlatformServiceFactory::create($shop);

            $result = $service->publishGoods([[
                'goods_name'     => $this->goods->goods_name,
                'sku'            => $platformSku->platform_sku,
                'price'          => $this->goods->selling_price,
                'stock'          => $this->goods->system_stock,
                'description'    => $this->goods->description,
                'images'         => $this->goods->images,
                'category'       => $this->goods->category,
                'brand'          => $this->goods->brand,
            ]]);

            $success = !empty($result['results'][0]['success']);
            $errorMsg = $result['results'][0]['error'] ?? null;

            // 使用事务更新发布状态
            DB::transaction(function () use ($publishRecord, $success, $errorMsg, $shop) {
                if ($success) {
                    $publishRecord->update([
                        'publish_status' => 'success',
                        'published_at'   => now(),
                    ]);
                    Log::info("商品发布成功: {$this->goods->goods_name} -> {$shop->shop_name}");
                } else {
                    $publishRecord->update([
                        'publish_status'  => 'failed',
                        'error_message'   => $errorMsg ?? '发布失败',
                    ]);
                    Log::error("商品发布失败: {$this->goods->goods_name} -> {$shop->shop_name}");
                }
            });
        } catch (\Exception $e) {
            // 使用事务更新失败状态
            DB::transaction(function () use ($publishRecord, $e) {
                $publishRecord->update([
                    'publish_status' => 'failed',
                    'error_message'  => $e->getMessage(),
                ]);
            });

            Log::error("商品发布异常: " . $e->getMessage(), [
                'goods_id' => $this->goods->id,
                'shop_id' => $this->shopId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
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
        Log::error("商品推送任务最终失败", [
            'goods_id' => $this->goods->id,
            'shop_id' => $this->shopId,
            'error' => $exception->getMessage(),
        ]);

        // 更新发布记录状态为失败
        if ($this->publishRecordId) {
            GoodsPublishRecord::where('id', $this->publishRecordId)
                ->update([
                    'publish_status' => 'failed',
                    'error_message'  => $exception->getMessage(),
                ]);
        }

        // 发送失败通知给超级管理员
        $superAdmins = User::role('super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SyncFailedNotification([
                'task_type' => 'goods_push',
                'task_name' => '商品推送',
                'shop_id' => $this->shopId,
                'shop_name' => \App\Soho\Models\Shop::find($this->shopId)?->shop_name ?? '未知店铺',
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
        return "goods_push_{$this->goods->id}_{$this->shopId}";
    }
}