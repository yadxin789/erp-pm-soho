<?php

namespace App\Soho\Jobs;

use App\Soho\Models\PlatformOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 事务回滚测试队列任务
 * 用于验证事务回滚机制是否正常工作
 */
class TestTransactionRollbackJob implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     * @var int
     */
    public int $tries = 1;

    /**
     * 是否模拟失败
     * @var bool
     */
    protected bool $shouldFail = false;

    /**
     * 创建任务实例
     *
     * @param PlatformOrder $order 订单
     * @param bool $shouldFail 是否模拟失败
     */
    public function __construct(
        protected PlatformOrder $order,
        bool $shouldFail = false,
    ) {
        $this->shouldFail = $shouldFail;
    }

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info("开始事务回滚测试任务", [
            'order_id' => $this->order->id,
            'platform_order_no' => $this->order->platform_order_no,
            'should_fail' => $this->shouldFail,
        ]);

        try {
            // 使用数据库事务
            DB::transaction(function () {
                // 步骤1: 更新订单状态为处理中
                $this->order->update([
                    'order_status' => 'processing',
                ]);
                Log::info("步骤1完成: 订单状态更新为processing", [
                    'order_id' => $this->order->id,
                ]);

                // 步骤2: 更新订单备注
                $this->order->update([
                    'operator_remark' => '事务测试处理中...',
                ]);
                Log::info("步骤2完成: 订单备注已更新", [
                    'order_id' => $this->order->id,
                ]);

                // 步骤3: 如果设置了模拟失败，则抛出异常
                if ($this->shouldFail) {
                    Log::warning("步骤3: 模拟事务失败，触发回滚", [
                        'order_id' => $this->order->id,
                    ]);
                    throw new \Exception("模拟事务失败，测试回滚机制");
                }

                // 步骤4: 更新订单状态为已完成
                $this->order->update([
                    'order_status' => 'completed',
                    'completed_time' => now(),
                ]);
                Log::info("步骤4完成: 订单状态更新为completed", [
                    'order_id' => $this->order->id,
                ]);
            });

            Log::info("事务回滚测试任务成功完成", [
                'order_id' => $this->order->id,
                'final_status' => $this->order->refresh()->order_status,
            ]);
        } catch (\Exception $e) {
            Log::error("事务回滚测试任务失败", [
                'order_id' => $this->order->id,
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
        Log::error("事务回滚测试任务最终失败", [
            'order_id' => $this->order->id,
            'platform_order_no' => $this->order->platform_order_no,
            'error' => $exception->getMessage(),
        ]);

        // 验证事务是否正确回滚
        $this->order->refresh();
        if ($this->order->order_status !== 'processing' && $this->order->order_status !== 'completed') {
            Log::info("✅ 事务回滚成功！订单状态恢复为: {$this->order->order_status}", [
                'order_id' => $this->order->id,
            ]);
        } else {
            Log::error("❌ 事务回滚失败！订单状态未恢复", [
                'order_id' => $this->order->id,
                'current_status' => $this->order->order_status,
            ]);
        }
    }
}