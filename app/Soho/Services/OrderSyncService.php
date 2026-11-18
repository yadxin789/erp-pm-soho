<?php

namespace App\Soho\Services;

use App\Soho\Models\OrderItem;
use App\Soho\Models\OrderStatusLog;
use App\Soho\Models\PlatformOrder;
use App\Soho\Models\Shop;
use App\Soho\Models\SyncTaskLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 订单同步服务
 * 负责从各平台同步订单数据到本地数据库
 */
class OrderSyncService
{
    /**
     * 同步所有店铺的订单
     *
     * @return array 各店铺同步结果
     */
    public function syncAll(): array
    {
        $shops = Shop::where('status', 1)
            ->where('auth_status', 1)
            ->where('sync_enabled', 1)
            ->get();

        $results = [];
        foreach ($shops as $shop) {
            $results[$shop->id] = $this->syncShopOrders($shop);
        }

        return $results;
    }

    /**
     * 同步指定店铺的订单
     *
     * @param Shop $shop 店铺模型
     * @return array 同步结果
     */
    public function syncShopOrders(Shop $shop): array
    {
        $taskLog = SyncTaskLog::create([
            'task_name' => "{$shop->platform_label} - {$shop->shop_name} 订单同步",
            'task_type' => 'order_sync',
            'platform'  => $shop->platform,
            'shop_id'   => $shop->id,
            'status'    => 'running',
            'started_at'=> now(),
        ]);

        try {
            $service = PlatformServiceFactory::create($shop);
            $orders = $service->fetchOrders([
                'start_time' => now()->subMinutes(30),
                'page_size'  => 100,
            ]);

            $savedCount = 0;
            foreach ($orders as $orderData) {
                if ($this->saveOrder($shop, $orderData)) {
                    $savedCount++;
                }
            }

            $taskLog->update([
                'status'        => 'success',
                'total_count'   => count($orders),
                'success_count' => $savedCount,
                'fail_count'    => count($orders) - $savedCount,
                'finished_at'   => now(),
            ]);

            return ['success' => true, 'saved' => $savedCount];
        } catch (\Exception $e) {
            $taskLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 保存订单到数据库（包含订单明细和状态日志）
     * 校验订单是否已存在（幂等性处理），不存在则创建
     *
     * @param Shop $shop 店铺模型
     * @param array $orderData 订单数据
     * @return bool 是否保存成功
     */
    protected function saveOrder(Shop $shop, array $orderData): bool
    {
        try {
            // 使用platform_order_no作为唯一标识进行幂等判断
            $platformOrderNo = $orderData['platform_order_no'] ?? '';
            
            if (empty($platformOrderNo)) {
                Log::warning('订单数据缺少平台订单号', ['order_data' => $orderData]);
                return false;
            }

            $exists = PlatformOrder::where('platform_order_no', $platformOrderNo)
                ->where('platform', $shop->platform)
                ->exists();

            if ($exists) {
                return false;
            }

            DB::beginTransaction();

            // 创建订单主记录
            $order = PlatformOrder::create([
                'platform_order_no' => $platformOrderNo,
                'system_order_no'   => 'SYS' . date('YmdHis') . rand(1000, 9999),
                'platform'          => $shop->platform,
                'shop_id'           => $shop->id,
                'order_status'      => $orderData['order_status'] ?? 'pending',
                'pay_status'        => $orderData['pay_status'] ?? 'unpaid',
                'total_amount'      => $orderData['total_amount'] ?? 0,
                'pay_amount'        => $orderData['pay_amount'] ?? 0,
                'currency'          => $orderData['currency'] ?? 'CNY',
                'buyer_nick'        => $orderData['buyer_nick'] ?? '',
                'receiver_name'     => $orderData['receiver_name'] ?? '',
                'receiver_phone'    => $orderData['receiver_phone'] ?? '',
                'receiver_province' => $orderData['receiver_province'] ?? '',
                'receiver_city'     => $orderData['receiver_city'] ?? '',
                'receiver_district' => $orderData['receiver_district'] ?? '',
                'receiver_address'  => $orderData['receiver_address'] ?? '',
                'order_created_at'  => $orderData['order_created_at'] ?? now(),
                'pay_time'          => $orderData['pay_time'] ?? null,
                'ship_time'         => $orderData['ship_time'] ?? null,
                'order_detail'      => $orderData['order_detail'] ?? $orderData['platform_raw_data'] ?? [],
                'platform_raw_data' => $orderData,
            ]);

            // 创建订单明细
            $this->saveOrderItems($order, $orderData);

            // 创建订单状态日志
            $this->saveOrderStatusLog($order);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('保存订单失败', [
                'shop_id' => $shop->id,
                'order_data' => $orderData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * 保存订单明细
     * 根据平台返回的数据解析订单商品明细
     *
     * @param PlatformOrder $order 订单模型
     * @param array $orderData 原始订单数据
     */
    protected function saveOrderItems(PlatformOrder $order, array $orderData): void
    {
        $items = $this->extractOrderItems($orderData);

        foreach ($items as $itemData) {
            OrderItem::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'sku_id' => $itemData['sku_id'] ?? null,
                'platform_sku_id' => $itemData['platform_sku_id'] ?? '',
                'product_name' => $itemData['product_name'] ?? '',
                'sku_name' => $itemData['sku_name'] ?? '',
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'refund_amount' => $itemData['refund_amount'] ?? 0,
                'cost_price' => $itemData['cost_price'] ?? 0,
                'profit_amount' => $this->calculateProfit(
                    $itemData['unit_price'] ?? 0,
                    $itemData['cost_price'] ?? 0,
                    $itemData['quantity'] ?? 1,
                    $itemData['discount_amount'] ?? 0
                ),
                'warehouse_id' => $itemData['warehouse_id'] ?? null,
                'fulfillment_status' => $itemData['fulfillment_status'] ?? 'pending',
                'raw_data' => $itemData['raw_data'] ?? [],
            ]);
        }
    }

    /**
     * 从平台原始数据中提取订单明细
     * 不同平台数据格式不同，需要适配
     *
     * @param array $orderData 原始订单数据
     * @return array 订单明细列表
     */
    protected function extractOrderItems(array $orderData): array
    {
        $items = [];
        $platform = $orderData['platform'] ?? '';
        $rawData = $orderData['platform_raw_data'] ?? [];

        switch ($platform) {
            case 'taobao':
                // 淘宝订单明细通常在trade.items.item中
                $taobaoItems = $rawData['items']['item'] ?? [];
                if (!is_array($taobaoItems)) {
                    $taobaoItems = [$taobaoItems];
                }
                foreach ($taobaoItems as $item) {
                    $items[] = [
                        'platform_sku_id' => $item['sku_id'] ?? '',
                        'product_name' => $item['title'] ?? '',
                        'sku_name' => $item['sku_properties_name'] ?? '',
                        'quantity' => $item['num'] ?? 1,
                        'unit_price' => $item['price'] ?? 0,
                        'raw_data' => $item,
                    ];
                }
                break;

            case 'jd':
                // 京东订单明细通常在order_info.items.item_info_list中
                $jdItems = $rawData['items']['item_info_list'] ?? $rawData['soho_order_items'] ?? [];
                if (!is_array($jdItems)) {
                    $jdItems = [$jdItems];
                }
                foreach ($jdItems as $item) {
                    $items[] = [
                        'platform_sku_id' => $item['sku_id'] ?? '',
                        'product_name' => $item['item_name'] ?? '',
                        'sku_name' => $item['sku_name'] ?? '',
                        'quantity' => $item['item_num'] ?? 1,
                        'unit_price' => $item['item_price'] ?? 0,
                        'raw_data' => $item,
                    ];
                }
                break;

            case 'pinduoduo':
                // 拼多多订单明细通常在order_list中的goods_list
                $pddItems = $rawData['goods_list'] ?? [];
                foreach ($pddItems as $item) {
                    $items[] = [
                        'platform_sku_id' => $item['sku_id'] ?? '',
                        'product_name' => $item['goods_name'] ?? '',
                        'sku_name' => $item['goods_spec'] ?? '',
                        'quantity' => $item['goods_num'] ?? 1,
                        'unit_price' => $item['goods_price'] ?? 0,
                        'raw_data' => $item,
                    ];
                }
                break;

            case 'amazon':
                // 亚马逊订单明细需要单独调用API获取，这里暂存空数据
                // 实际业务中需要调用getOrderItems API
                $items[] = [
                    'platform_sku_id' => '',
                    'product_name' => $rawData['OrderTotal']['Amount'] ?? '商品',
                    'sku_name' => '',
                    'quantity' => 1,
                    'unit_price' => $rawData['OrderTotal']['Amount'] ?? 0,
                    'raw_data' => $rawData,
                ];
                break;

            default:
                // 通用处理：尝试从order_detail中提取
                $detailItems = $orderData['order_detail'] ?? [];
                if (is_array($detailItems) && !empty($detailItems)) {
                    foreach ($detailItems as $item) {
                        $items[] = [
                            'platform_sku_id' => $item['sku_id'] ?? $item['platform_sku_id'] ?? '',
                            'product_name' => $item['product_name'] ?? $item['name'] ?? '',
                            'sku_name' => $item['sku_name'] ?? '',
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? $item['price'] ?? 0,
                            'raw_data' => $item,
                        ];
                    }
                }
                break;
        }

        return $items;
    }

    /**
     * 计算单品利润
     *
     * @param float $unitPrice 单价
     * @param float $costPrice 成本价
     * @param int $quantity 数量
     * @param float $discountAmount 折扣金额
     * @return float 利润金额
     */
    protected function calculateProfit(float $unitPrice, float $costPrice, int $quantity, float $discountAmount): float
    {
        $revenue = $unitPrice * $quantity - $discountAmount;
        $cost = $costPrice * $quantity;
        return round($revenue - $cost, 2);
    }

    /**
     * 保存订单状态日志
     * 记录订单创建时的初始状态
     *
     * @param PlatformOrder $order 订单模型
     */
    protected function saveOrderStatusLog(PlatformOrder $order): void
    {
        OrderStatusLog::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'from_status' => '',
            'to_status' => $order->order_status,
            'operator_type' => 'system',
            'operator_id' => null,
            'source' => 'sync',
            'remark' => '订单同步创建',
        ]);
    }
}
