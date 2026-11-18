<?php

namespace App\Soho\Jobs;

use App\Soho\Models\PlatformOrder;
use App\Soho\Models\SystemShipRecord;
use App\Soho\Models\User;
use App\Soho\Notifications\SyncFailedNotification;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * 发货推送队列任务
 * 将发货信息推送至电商平台
 */
class PushShipmentJob implements ShouldQueue
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
    public int $timeout = 60;

    /**
     * 发货记录ID
     * @var int
     */
    protected int $shipRecordId;

    /**
     * 创建任务实例
     *
     * @param SystemShipRecord $shipRecord 发货记录
     */
    public function __construct(
        protected SystemShipRecord $shipRecord,
    ) {
        $this->shipRecordId = $shipRecord->id;
        $this->onQueue('shipment');
    }

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle(): void
    {
        // 重新获取发货记录，确保数据最新
        $shipRecord = SystemShipRecord::find($this->shipRecordId);
        
        if (!$shipRecord) {
            Log::warning("发货记录不存在", ['ship_record_id' => $this->shipRecordId]);
            return;
        }

        // 幂等性保护：已成功或正在处理的发货记录跳过
        if ($shipRecord->ship_status === 'success') {
            Log::info("发货记录 {$shipRecord->id} 已成功推送，跳过重复处理");
            return;
        }

        // 刷新模型以获取最新状态（防止并发重复）
        $shipRecord->refresh();

        if (in_array($shipRecord->ship_status, ['success', 'processing'])) {
            Log::info("发货记录 {$shipRecord->id} 状态为 {$shipRecord->ship_status}，跳过处理");
            return;
        }

        $order = $shipRecord->platformOrder;
        if (!$order) {
            Log::error("发货记录关联的订单不存在", ['ship_record_id' => $shipRecord->id]);
            return;
        }

        $shop = $order->shop;
        if (!$shop) {
            Log::error("订单关联的店铺不存在", ['order_id' => $order->id]);
            return;
        }

        Log::info("推送发货信息: 订单 {$order->platform_order_no}", [
            'ship_record_id' => $shipRecord->id,
            'tracking_number' => $shipRecord->tracking_number,
        ]);

        try {
            // 使用数据库事务处理
            DB::transaction(function () use ($shipRecord) {
                // 标记为处理中，防止并发重复
                $shipRecord->update(['ship_status' => 'processing']);
            });

            $service = PlatformServiceFactory::create($shop);
            $expressCompany = $shipRecord->expressCompany;

            $result = $service->pushShipment([
                'platform_order_no' => $order->platform_order_no,
                'tracking_number'   => $shipRecord->tracking_number,
                'company_code'      => $expressCompany?->code,
                'company_name'      => $expressCompany?->name,
            ]);

            if ($result['success']) {
                // 使用事务更新发货记录和订单状态
                DB::transaction(function () use ($shipRecord, $order) {
                    $shipRecord->update([
                        'ship_status' => 'success',
                        'ship_time'   => now(),
                    ]);

                    $order->update([
                        'order_status' => 'shipped',
                        'ship_time'    => now(),
                    ]);
                });

                Log::info("发货推送成功: 订单 {$order->platform_order_no}");
            } else {
                // 使用事务更新失败状态
                DB::transaction(function () use ($shipRecord, $result) {
                    $shipRecord->update([
                        'ship_status' => 'failed',
                        'ship_error'  => $result['error'] ?? '推送失败',
                    ]);
                });

                Log::error("发货推送失败: 订单 {$order->platform_order_no}, 原因: " . ($result['error'] ?? '未知'));
            }
        } catch (\Exception $e) {
            // 使用事务更新异常状态
            DB::transaction(function () use ($shipRecord, $e) {
                $shipRecord->update([
                    'ship_status' => 'failed',
                    'ship_error'  => $e->getMessage(),
                ]);
            });

            Log::error("发货推送异常: 订单 {$order->platform_order_no}, " . $e->getMessage());
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
        Log::error("发货推送任务最终失败", [
            'ship_record_id' => $this->shipRecordId,
            'error' => $exception->getMessage(),
        ]);

        // 更新发货记录状态为失败
        $shipRecord = SystemShipRecord::find($this->shipRecordId);
        if ($shipRecord) {
            $order = $shipRecord->platformOrder;
            
            DB::transaction(function () use ($shipRecord, $exception) {
                $shipRecord->update([
                    'ship_status' => 'failed',
                    'ship_error'  => $exception->getMessage(),
                ]);
            });

            // 发送失败通知给超级管理员
            $superAdmins = User::role('super_admin')->get();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new SyncFailedNotification([
                    'task_type' => 'shipment_push',
                    'task_name' => '发货推送',
                    'shop_id' => $order?->shop_id,
                    'shop_name' => $order?->shop?->shop_name ?? '未知店铺',
                    'error_message' => $exception->getMessage(),
                    'attempts' => $this->tries,
                    'failed_at' => now()->toDateTimeString(),
                ]));
            }
        }
    }

    /**
     * 获取任务唯一标识
     *
     * @return string
     */
    public function getJobId(): string
    {
        return "shipment_push_{$this->shipRecordId}";
    }
}