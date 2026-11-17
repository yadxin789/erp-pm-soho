<?php

namespace App\Soho\Console\Commands;

use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Shop;
use App\Core\Models\User;
use App\Soho\Jobs\TestTransactionRollbackJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

/**
 * 队列推送测试命令
 * 专门测试队列分发和执行功能
 */
class TestQueuePushJob extends Command
{
    /**
     * 命令名称
     * @var string
     */
    protected $signature = 'soho:test-queue-push {--sync : 使用同步队列执行}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = '测试队列推送功能';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('队列推送测试');
        $this->info('==========================================');
        $this->newLine();

        // 创建测试店铺和订单
        $shop = Shop::firstOrCreate(
            ['shop_code' => 'test_queue_shop'],
            [
                'platform' => 'taobao',
                'shop_name' => '队列测试店铺',
                'app_key' => 'test',
                'app_secret' => 'test',
                'auth_status' => 1,
                'status' => true,
            ]
        );

        $order = PlatformOrder::create([
            'platform_order_no' => 'QUEUE_TEST_' . date('YmdHis'),
            'platform' => 'taobao',
            'shop_id' => $shop->id,
            'order_status' => 'paid',
            'pay_status' => 'paid',
            'total_amount' => 99.99,
            'pay_amount' => 99.99,
            'currency' => 'CNY',
            'buyer_nick' => 'test_buyer',
            'receiver_name' => '测试用户',
            'receiver_phone' => '13800000000',
            'receiver_address' => '测试地址',
            'order_created_at' => now(),
            'pay_time' => now(),
            'order_detail' => [],
            'platform_raw_data' => [],
        ]);

        $this->info("✅ 创建测试订单: {$order->platform_order_no}");
        $this->info("   初始状态: {$order->order_status}");
        $this->newLine();

        // 测试队列推送
        $this->info('正在分发队列任务...');
        
        if ($this->option('sync')) {
            $this->info('模式: 同步队列 (sync)');
            Bus::dispatchSync(new TestTransactionRollbackJob($order, false));
        } else {
            $this->info('模式: 异步队列 (database)');
            TestTransactionRollbackJob::dispatch($order)->onQueue('default');
            $this->info('✅ 任务已加入队列');
            $this->info('⚠️ 请运行: php artisan queue:work --queue=default');
        }

        $this->newLine();
        
        // 验证结果
        $order->refresh();
        if ($this->option('sync')) {
            if ($order->order_status === 'completed') {
                $this->info('✅ 同步队列测试成功！订单状态: completed');
            } else {
                $this->error("❌ 同步队列测试失败！订单状态: {$order->order_status}");
                return 1;
            }
        } else {
            $this->info("当前订单状态: {$order->order_status}");
            $this->info('等待队列消费者处理...');
        }

        $this->newLine();
        $this->info('==========================================');
        $this->info('测试完成');
        $this->info('==========================================');

        return 0;
    }
}