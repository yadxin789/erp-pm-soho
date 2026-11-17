<?php

namespace App\Soho\Console\Commands;

use App\Soho\Models\ExpressCompany;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Shop;
use App\Soho\Models\SystemShipRecord;
use App\Core\Models\User;
use App\Soho\Jobs\PushShipmentJob;
use App\Soho\Jobs\TestTransactionRollbackJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 队列和事务回滚测试命令
 * 用于验证队列推送和事务回滚功能是否正常工作
 */
class TestQueueTransaction extends Command
{
    /**
     * 命令名称
     * @var string
     */
    protected $signature = 'soho:test-queue-transaction 
                            {--fail : 模拟失败场景，测试事务回滚}
                            {--transaction : 专门测试事务回滚机制}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = '测试队列推送和事务回滚功能';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('队列和事务回滚测试');
        $this->info('==========================================');
        $this->newLine();

        // 检查数据库连接
        try {
            DB::connection()->getPdo();
            $this->info('✅ 数据库连接正常');
        } catch (\Exception $e) {
            $this->error('❌ 数据库连接失败: ' . $e->getMessage());
            return 1;
        }

        // 创建测试数据
        $this->info('正在创建测试数据...');
        $shop = $this->createTestShop();
        $order = $this->createTestOrder($shop);
        $expressCompany = $this->createTestExpressCompany();

        $this->info("✅ 测试店铺: {$shop->platform_label} - {$shop->shop_name}");
        $this->info("✅ 测试订单: {$order->platform_order_no} (状态: {$order->order_status_label})");
        $this->info("✅ 快递公司: {$expressCompany->name}");
        $this->newLine();

        // 创建发货记录并测试队列
        $shipRecord = $this->createTestShipRecord($order, $expressCompany);
        $this->info("✅ 发货记录创建成功: ID={$shipRecord->id}, 单号={$shipRecord->tracking_number}");
        $this->info("   当前发货状态: {$shipRecord->ship_status}");
        $this->newLine();

        // 测试队列推送
        $this->info('正在测试队列推送...');
        
        // 模拟失败场景
        if ($this->option('fail')) {
            $this->info('⚠️ 模拟失败场景 - 将触发事务回滚');
            
            // 直接调用handle方法测试失败逻辑
            $job = new PushShipmentJob($shipRecord);
            
            try {
                $job->handle();
                $this->error('❌ 预期会失败，但执行成功了');
                return 1;
            } catch (\Exception $e) {
                $this->info("✅ 捕获到预期异常: {$e->getMessage()}");
                
                // 调用failed方法测试失败处理
                $job->failed($e);
                
                // 检查发货记录状态是否正确更新为failed
                $shipRecord->refresh();
                if ($shipRecord->ship_status === 'failed') {
                    $this->info("✅ 发货记录状态正确回滚为: {$shipRecord->ship_status}");
                    $this->info("✅ 错误信息已记录: {$shipRecord->ship_error}");
                } else {
                    $this->error("❌ 发货记录状态未正确回滚: {$shipRecord->ship_status}");
                    return 1;
                }
            }
        } else {
            // 正常测试场景
            $this->info('📦 正常测试场景 - 测试队列分发');
            
            // 分发到队列
            PushShipmentJob::dispatch($shipRecord)
                ->onQueue('default');
            
            $this->info('✅ 队列任务已分发');
            $this->info('⚠️ 请运行以下命令启动队列消费者以处理任务:');
            $this->info('   php artisan queue:work --queue=default');
            $this->newLine();
            $this->info('任务信息:');
            $this->info("   Job类: PushShipmentJob");
            $this->info("   队列: default");
            $this->info("   发货记录ID: {$shipRecord->id}");
            $this->info("   订单号: {$order->platform_order_no}");
        }

        // 专门测试事务回滚机制
        if ($this->option('transaction')) {
            $this->newLine();
            $this->info('==========================================');
            $this->info('事务回滚机制测试');
            $this->info('==========================================');
            $this->newLine();

            // 创建专门的测试订单
            $testOrder = $this->createTestOrder($shop);
            $this->info("创建测试订单: {$testOrder->platform_order_no}");
            $this->info("初始状态: {$testOrder->order_status}");
            
            // 测试1: 正常事务
            $this->newLine();
            $this->info('测试1: 正常事务执行');
            $job1 = new TestTransactionRollbackJob($testOrder, false);
            $job1->handle();
            $testOrder->refresh();
            if ($testOrder->order_status === 'completed') {
                $this->info('✅ 正常事务测试通过，订单状态更新为: completed');
            } else {
                $this->error("❌ 正常事务测试失败，订单状态: {$testOrder->order_status}");
                return 1;
            }

            // 测试2: 事务回滚
            $this->newLine();
            $testOrder2 = $this->createTestOrder($shop);
            $this->info('测试2: 事务回滚测试');
            $this->info("创建测试订单: {$testOrder2->platform_order_no}");
            $this->info("初始状态: {$testOrder2->order_status}");
            
            $job2 = new TestTransactionRollbackJob($testOrder2, true);
            try {
                $job2->handle();
                $this->error('❌ 预期会抛出异常');
                return 1;
            } catch (\Exception $e) {
                $this->info("✅ 捕获到异常: {$e->getMessage()}");
                $job2->failed($e);
                
                $testOrder2->refresh();
                if ($testOrder2->order_status === 'paid') {
                    $this->info('✅ 事务回滚测试通过，订单状态恢复为: paid');
                } else {
                    $this->error("❌ 事务回滚测试失败，订单状态: {$testOrder2->order_status}");
                    return 1;
                }
            }
        }

        $this->newLine();
        $this->info('==========================================');
        $this->info('测试完成');
        $this->info('==========================================');

        return 0;
    }

    /**
     * 创建测试店铺
     *
     * @return Shop
     */
    protected function createTestShop(): Shop
    {
        return Shop::firstOrCreate(
            ['shop_code' => 'test_shop_001'],
            [
                'platform' => 'taobao',
                'shop_name' => '测试店铺',
                'app_key' => 'test_key',
                'app_secret' => 'test_secret',
                'access_token' => 'test_token',
                'token_expires_at' => now()->addDays(7),
                'auth_status' => 1,
                'status' => true,
                'sync_enabled' => true,
            ]
        );
    }

    /**
     * 创建测试订单
     *
     * @param Shop $shop
     * @return PlatformOrder
     */
    protected function createTestOrder(Shop $shop): PlatformOrder
    {
        return PlatformOrder::create([
            'platform_order_no' => 'TB' . date('YmdHis') . mt_rand(1000, 9999),
            'system_order_no' => 'DD' . date('Ymd') . mt_rand(100000, 999999),
            'platform' => 'taobao',
            'shop_id' => $shop->id,
            'order_status' => 'paid',
            'pay_status' => 'paid',
            'total_amount' => 199.99,
            'pay_amount' => 199.99,
            'discount_amount' => 0,
            'freight_amount' => 10.00,
            'currency' => 'CNY',
            'buyer_nick' => '测试买家' . mt_rand(1000, 9999),
            'buyer_message' => '尽快发货',
            'receiver_name' => '张三',
            'receiver_phone' => '13800138000',
            'receiver_province' => '广东省',
            'receiver_city' => '深圳市',
            'receiver_district' => '南山区',
            'receiver_address' => '科技园路88号',
            'receiver_zip' => '518000',
            'order_created_at' => now()->subHours(2),
            'pay_time' => now()->subHour(),
            'order_detail' => [
                [
                    'goods_name' => '测试商品',
                    'sku' => 'SKU001',
                    'quantity' => 1,
                    'price' => 199.99,
                ],
            ],
            'platform_raw_data' => [],
        ]);
    }

    /**
     * 创建测试快递公司
     *
     * @return ExpressCompany
     */
    protected function createTestExpressCompany(): ExpressCompany
    {
        return ExpressCompany::firstOrCreate(
            ['code' => 'SF'],
            [
                'name' => '顺丰速运',
                'logo' => '',
                'status' => true,
            ]
        );
    }

    /**
     * 创建测试发货记录
     *
     * @param PlatformOrder $order
     * @param ExpressCompany $expressCompany
     * @return SystemShipRecord
     */
    protected function createTestShipRecord(PlatformOrder $order, ExpressCompany $expressCompany): SystemShipRecord
    {
        return SystemShipRecord::create([
            'platform_order_id' => $order->id,
            'express_company_id' => $expressCompany->id,
            'tracking_number' => 'SF' . date('Ymd') . mt_rand(100000000000, 999999999999),
            'ship_status' => 'pending',
            'operator_id' => $this->getTestUserId(),
        ]);
    }

    /**
     * 获取测试用户ID
     *
     * @return int|null
     */
    protected function getTestUserId(): ?int
    {
        $user = User::first();
        if ($user) {
            return $user->id;
        }
        
        // 创建测试用户
        $user = User::create([
            'name' => 'test_admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'real_name' => '测试管理员',
            'status' => true,
        ]);
        
        return $user->id;
    }
}