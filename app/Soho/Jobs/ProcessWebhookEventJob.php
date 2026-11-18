<?php

namespace App\Soho\Jobs;

use App\Soho\Models\OrderAfterSale;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\WebhookEventLog;
use App\Soho\Services\OrderSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Webhook事件处理Job
 * 异步处理Webhook事件，支持重试机制
 */
class ProcessWebhookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    protected string $platform;

    protected string $eventType;

    protected array $data;

    protected WebhookEventLog $log;

    public function __construct(string $platform, string $eventType, array $data, WebhookEventLog $log)
    {
        $this->platform = $platform;
        $this->eventType = $eventType;
        $this->data = $data;
        $this->log = $log;
    }

    public function handle(): void
    {
        try {
            match ($this->eventType) {
                'order' => $this->processOrderEvent(),
                'inventory' => $this->processInventoryEvent(),
                'after_sale' => $this->processAfterSaleEvent(),
                default => $this->logGenericEvent(),
            };

            $this->log->markSuccess();
        } catch (\Exception $e) {
            $this->log->markFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * 处理订单事件：同步订单状态到本地
     */
    protected function processOrderEvent(): void
    {
        $platformOrderNo = $this->extractOrderNo();
        if (!$platformOrderNo) {
            Log::warning('Webhook order event missing order_no', ['platform' => $this->platform, 'data' => $this->data]);
            return;
        }

        $orderStatus = $this->extractOrderStatus();
        $platformStatusMapping = config("platforms.{$this->platform}.order_status_mapping", []);
        $unifiedStatus = $platformStatusMapping[$orderStatus] ?? $orderStatus;

        $order = PlatformOrder::where('platform_order_no', $platformOrderNo)
            ->where('platform', $this->platform)
            ->first();

        if ($order) {
            $oldStatus = $order->order_status;
            if ($oldStatus !== $unifiedStatus) {
                DB::transaction(function () use ($order, $oldStatus, $unifiedStatus, $orderStatus) {
                    $order->update(['order_status' => $unifiedStatus]);

                    if ($unifiedStatus === 'paid' && isset($this->data['pay_time'])) {
                        $order->update(['pay_time' => $this->data['pay_time']]);
                    }
                    if ($unifiedStatus === 'shipped' && isset($this->data['ship_time'])) {
                        $order->update(['ship_time' => $this->data['ship_time']]);
                    }
                    if ($unifiedStatus === 'completed') {
                        $order->update(['completed_time' => now()]);
                    }

                    \App\Soho\Models\OrderStatusLog::create([
                        'tenant_id' => $order->tenant_id,
                        'order_id' => $order->id,
                        'from_status' => $oldStatus,
                        'to_status' => $unifiedStatus,
                        'operator_type' => 'webhook',
                        'source' => "{$this->platform}_webhook",
                        'remark' => "平台状态变更: {$orderStatus} → {$unifiedStatus}",
                    ]);
                });

                Log::info('Order status updated via webhook', [
                    'platform_order_no' => $platformOrderNo,
                    'old_status' => $oldStatus,
                    'new_status' => $unifiedStatus,
                ]);
            }
        } else {
            $this->syncNewOrder($platformOrderNo);
        }
    }

    /**
     * 处理库存事件：记录库存变更
     */
    protected function processInventoryEvent(): void
    {
        $skuCode = $this->data['sku'] ?? $this->data['sku_code'] ?? '';
        $quantity = $this->data['quantity'] ?? $this->data['stock'] ?? null;
        $platformSkuId = $this->data['platform_sku_id'] ?? $this->data['goods_id'] ?? '';

        if ($quantity === null) {
            Log::warning('Webhook inventory event missing quantity', ['data' => $this->data]);
            return;
        }

        $platformSku = \App\Soho\Models\PlatformSku::where('shop_id', $this->findShopId())
            ->where(function ($q) use ($platformSkuId, $skuCode) {
                if ($platformSkuId) {
                    $q->where('platform_goods_id', $platformSkuId);
                } elseif ($skuCode) {
                    $q->where('platform_sku', $skuCode);
                }
            })
            ->first();

        if ($platformSku && $platformSku->systemGoods) {
            $oldStock = $platformSku->systemGoods->system_stock;
            $platformSku->update([
                'platform_stock' => $quantity,
                'last_sync_at' => now(),
            ]);

            Log::info('Platform stock updated via webhook', [
                'platform' => $this->platform,
                'platform_sku_id' => $platformSkuId,
                'old_stock' => $oldStock,
                'new_stock' => $quantity,
            ]);
        }
    }

    /**
     * 处理售后事件：同步售后状态
     */
    protected function processAfterSaleEvent(): void
    {
        $afterSaleNo = $this->data['after_sale_no'] ?? $this->data['refund_id'] ?? '';
        $status = $this->data['status'] ?? $this->data['refund_status'] ?? '';

        if (!$afterSaleNo) {
            Log::warning('Webhook after-sale event missing after_sale_no', ['data' => $this->data]);
            return;
        }

        $unifiedStatus = match ($status) {
            'success', 'refunded', 'completed' => 'completed',
            'rejected', 'denied' => 'rejected',
            'processing', 'pending' => 'processing',
            default => $status,
        };

        $afterSale = OrderAfterSale::where('after_sale_no', $afterSaleNo)
            ->where('platform', $this->platform)
            ->first();

        if ($afterSale && $afterSale->status !== $unifiedStatus) {
            $afterSale->update([
                'status' => $unifiedStatus,
                'handle_time' => now(),
                'handle_result' => $this->data['reason'] ?? '',
            ]);

            Log::info('After-sale status updated via webhook', [
                'after_sale_no' => $afterSaleNo,
                'new_status' => $unifiedStatus,
            ]);
        }
    }

    /**
     * 记录未知事件类型
     */
    protected function logGenericEvent(): void
    {
        Log::info('Generic webhook event received', [
            'platform' => $this->platform,
            'event_type' => $this->eventType,
            'data' => $this->data,
        ]);
    }

    /**
     * 同步新订单（Webhook推送的订单不存在时尝试拉取）
     */
    protected function syncNewOrder(string $platformOrderNo): void
    {
        $shopId = $this->findShopId();
        if (!$shopId) {
            Log::warning('Cannot sync new order: shop not found', ['platform' => $this->platform]);
            return;
        }

        $syncService = app(OrderSyncService::class);
        $shop = \App\Soho\Models\Shop::find($shopId);
        if ($shop) {
            try {
                $syncService->syncShopOrders($shop);
            } catch (\Exception $e) {
                Log::error('Failed to sync new order from webhook', [
                    'platform_order_no' => $platformOrderNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * 从Webhook数据中提取订单号
     */
    protected function extractOrderNo(): ?string
    {
        return match ($this->platform) {
            'taobao' => $this->data['trade_id'] ?? $this->data['tid'] ?? $this->data['order_no'] ?? null,
            'jd' => $this->data['orderId'] ?? $this->data['order_id'] ?? null,
            'pinduoduo' => $this->data['order_sn'] ?? $this->data['order_no'] ?? null,
            'amazon' => $this->data['orderId'] ?? $this->data['AmazonOrderId'] ?? null,
            default => null,
        };
    }

    /**
     * 从Webhook数据中提取订单状态
     */
    protected function extractOrderStatus(): string
    {
        return match ($this->platform) {
            'taobao' => $this->data['status'] ?? $this->data['order_status'] ?? 'unknown',
            'jd' => $this->data['orderState'] ?? $this->data['order_status'] ?? 'unknown',
            'pinduoduo' => (string) ($this->data['order_status'] ?? $this->data['status'] ?? 'unknown'),
            'amazon' => $this->data['OrderStatus'] ?? $this->data['order_status'] ?? 'unknown',
            default => 'unknown',
        };
    }

    /**
     * 查找店铺ID
     */
    protected function findShopId(): ?int
    {
        return $this->data['shop_id'] ?? \App\Soho\Models\Shop::where('platform', $this->platform)
            ->where('status', 1)
            ->value('id');
    }

    public function failed(\Exception $exception): void
    {
        $this->log->markFailed($exception->getMessage());

        Log::error('Webhook event job failed after retries', [
            'platform' => $this->platform,
            'event_type' => $this->eventType,
            'event_id' => $this->log->event_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
