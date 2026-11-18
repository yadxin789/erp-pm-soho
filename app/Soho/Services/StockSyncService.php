<?php

namespace App\Soho\Services;

use App\Soho\Models\PlatformSku;
use App\Soho\Models\Shop;
use App\Soho\Models\StockSyncLog;
use App\Soho\Models\SyncTaskLog;

class StockSyncService
{
    public function syncAll(): array
    {
        $shops = Shop::where('status', 1)
            ->where('auth_status', 1)
            ->where('sync_enabled', 1)
            ->get();

        $results = [];
        foreach ($shops as $shop) {
            $results[$shop->id] = $this->syncShopStock($shop);
        }

        return $results;
    }

    public function syncShopStock(Shop $shop): array
    {
        $taskLog = SyncTaskLog::create([
            'task_name'   => "{$shop->platform_label} - {$shop->shop_name} 库存同步",
            'task_type'   => 'stock_sync',
            'platform'    => $shop->platform,
            'shop_id'     => $shop->id,
            'status'      => 'running',
            'started_at'  => now(),
        ]);

        try {
            $service = PlatformServiceFactory::create($shop);
            $platformSkus = PlatformSku::where('shop_id', $shop->id)
                ->where('stock_sync_enabled', 1)
                ->with('systemGoods')
                ->get();

            $successCount = 0;
            $failCount = 0;

            foreach ($platformSkus as $platformSku) {
                $result = $service->syncStock([
                    [
                        'system_goods_id'   => $platformSku->system_goods_id,
                        'platform_goods_id' => $platformSku->platform_goods_id,
                        'quantity'          => $platformSku->systemGoods->system_stock,
                        'sku'               => $platformSku->platform_sku,
                    ],
                ]);

                $synced = !empty($result['results'][0]['success']);
                $errorMsg = $result['results'][0]['error'] ?? null;

                StockSyncLog::create([
                    'system_goods_id' => $platformSku->system_goods_id,
                    'shop_id'         => $shop->id,
                    'platform'        => $shop->platform,
                    'before_stock'    => $platformSku->platform_stock ?? 0,
                    'after_stock'     => $platformSku->systemGoods->system_stock,
                    'sync_type'       => 'push',
                    'sync_status'     => $synced ? 'success' : 'failed',
                    'error_message'   => $errorMsg,
                ]);

                if ($synced) {
                    $platformSku->update([
                        'platform_stock' => $platformSku->systemGoods->system_stock,
                        'sync_status'    => 1,
                        'last_sync_at'   => now(),
                    ]);
                    $successCount++;
                } else {
                    $platformSku->update([
                        'sync_status' => 2,
                        'sync_error'  => $errorMsg ?? '同步失败',
                    ]);
                    $failCount++;
                }
            }

            $taskLog->update([
                'status'        => $failCount > 0 ? 'failed' : 'success',
                'total_count'   => $platformSkus->count(),
                'success_count' => $successCount,
                'fail_count'    => $failCount,
                'finished_at'   => now(),
            ]);

            return ['success' => true, 'synced' => $successCount, 'failed' => $failCount];
        } catch (\Exception $e) {
            $taskLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
