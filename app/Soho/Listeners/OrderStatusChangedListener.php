<?php

namespace App\Soho\Listeners;

use App\Soho\Events\OrderStatusChanged;
use App\Soho\Models\OperationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 订单状态变更监听器
 * 处理订单状态变更后的业务逻辑
 */
class OrderStatusChangedListener
{
    /**
     * 处理订单状态变更事件
     *
     * @param OrderStatusChanged $event 订单状态变更事件
     * @return void
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;

        Log::info("订单状态变更", [
            'order_id' => $order->id,
            'platform_order_no' => $order->platform_order_no,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // 使用事务记录操作日志
        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'order_status_changed',
                'model_type' => get_class($order),
                'model_id' => $order->id,
                'description' => "订单 {$order->platform_order_no} 状态从 {$oldStatus} 变更为 {$newStatus}",
                'changes' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // 根据新状态触发相应业务逻辑
        match ($newStatus) {
            'paid' => $this->handlePaidStatus($order),
            'shipped' => $this->handleShippedStatus($order),
            'completed' => $this->handleCompletedStatus($order),
            'cancelled' => $this->handleCancelledStatus($order),
            default => null,
        };
    }

    /**
     * 处理已付款状态
     *
     * @param mixed $order 订单
     * @return void
     */
    protected function handlePaidStatus($order): void
    {
        Log::info("订单已付款，等待发货", [
            'order_id' => $order->id,
            'platform_order_no' => $order->platform_order_no,
        ]);
    }

    /**
     * 处理已发货状态
     *
     * @param mixed $order 订单
     * @return void
     */
    protected function handleShippedStatus($order): void
    {
        Log::info("订单已发货", [
            'order_id' => $order->id,
            'platform_order_no' => $order->platform_order_no,
        ]);

        // 发货推送已由 ShipmentCreatedListener 处理，此处无需重复 dispatch
    }

    /**
     * 处理已完成状态
     *
     * @param mixed $order 订单
     * @return void
     */
    protected function handleCompletedStatus($order): void
    {
        Log::info("订单已完成", [
            'order_id' => $order->id,
            'platform_order_no' => $order->platform_order_no,
        ]);

        // 订单完成后的统计逻辑可以在此添加
    }

    /**
     * 处理已取消状态
     *
     * @param mixed $order 订单
     * @return void
     */
    protected function handleCancelledStatus($order): void
    {
        Log::info("订单已取消", [
            'order_id' => $order->id,
            'platform_order_no' => $order->platform_order_no,
        ]);

        // 订单取消后的库存回滚逻辑可以在此添加
    }
}