<?php

namespace App\Soho\Listeners;

use App\Soho\Events\StockChanged;
use App\Soho\Models\OperationLog;
use App\Soho\Models\StockSyncLog;
use App\Soho\Jobs\SyncSingleGoodsStockJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 库存变更监听器
 * 处理库存变更后的同步和日志记录
 */
class StockChangedListener
{
    /**
     * 处理库存变更事件
     *
     * @param StockChanged $event 库存变更事件
     * @return void
     */
    public function handle(StockChanged $event): void
    {
        $goods = $event->goods;
        $oldStock = $event->oldStock;
        $newStock = $event->newStock;
        $changeType = $event->changeType;

        Log::info("库存变更事件", [
            'goods_id' => $goods->id,
            'goods_name' => $goods->goods_name,
            'system_sku' => $goods->system_sku,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'change_type' => $changeType,
        ]);

        // 使用事务记录操作日志
        DB::transaction(function () use ($goods, $oldStock, $newStock, $changeType) {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'stock_changed',
                'model_type' => get_class($goods),
                'model_id' => $goods->id,
                'description' => "商品 {$goods->goods_name} (SKU: {$goods->system_sku}) 库存变更: {$oldStock} -> {$newStock}",
                'changes' => [
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'change_type' => $changeType,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // 同步到所有关联平台（异步队列）
        if ($changeType !== 'sync') { // 避免循环同步
            SyncSingleGoodsStockJob::dispatch($goods)
                ->delay(now()->addSeconds(5)); // 延迟5秒，等待数据库事务提交

            Log::info("库存同步任务已加入队列", [
                'goods_id' => $goods->id,
                'goods_name' => $goods->goods_name,
            ]);
        }

        // 检查库存预警
        $this->checkStockWarning($goods, $newStock);
    }

    /**
     * 检查库存预警
     *
     * @param mixed $goods 商品
     * @param int $currentStock 当前库存
     * @return void
     */
    protected function checkStockWarning($goods, int $currentStock): void
    {
        // 检查是否低于预警阈值
        if ($goods->min_stock_warn > 0 && $currentStock <= $goods->min_stock_warn) {
            Log::warning("库存预警", [
                'goods_id' => $goods->id,
                'goods_name' => $goods->goods_name,
                'current_stock' => $currentStock,
                'min_stock_warn' => $goods->min_stock_warn,
                'deficit' => $goods->min_stock_warn - $currentStock,
            ]);
        }
    }
}