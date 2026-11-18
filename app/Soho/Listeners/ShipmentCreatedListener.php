<?php

namespace App\Soho\Listeners;

use App\Soho\Events\ShipmentCreated;
use App\Soho\Models\OperationLog;
use App\Soho\Jobs\PushShipmentJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 发货记录创建监听器
 * 处理发货记录创建后的推送逻辑
 */
class ShipmentCreatedListener
{
    /**
     * 处理发货记录创建事件
     *
     * @param ShipmentCreated $event 发货创建事件
     * @return void
     */
    public function handle(ShipmentCreated $event): void
    {
        $shipRecord = $event->shipRecord;
        $order = $shipRecord->platformOrder;

        Log::info("发货记录创建事件", [
            'ship_record_id' => $shipRecord->id,
            'order_id' => $order?->id,
            'tracking_number' => $shipRecord->tracking_number,
        ]);

        // 使用事务记录操作日志
        DB::transaction(function () use ($shipRecord, $order) {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'shipment_created',
                'model_type' => get_class($shipRecord),
                'model_id' => $shipRecord->id,
                'description' => "订单 {$order?->platform_order_no} 创建发货记录，快递：{$shipRecord->expressCompany?->name}，单号：{$shipRecord->tracking_number}",
                'changes' => [
                    'order_id' => $order?->id,
                    'express_company_id' => $shipRecord->express_company_id,
                    'tracking_number' => $shipRecord->tracking_number,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // 异步推送发货信息到电商平台
        PushShipmentJob::dispatch($shipRecord)
            ->delay(now()->addSeconds(3)); // 延迟3秒，确保数据库事务提交

        Log::info("发货推送任务已加入队列", [
            'ship_record_id' => $shipRecord->id,
            'tracking_number' => $shipRecord->tracking_number,
        ]);
    }
}