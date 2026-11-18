<?php

namespace App\Soho\Services;

use Illuminate\Support\Facades\Http;

class PinduoduoService extends BasePlatformService
{
    protected string $apiGateway;

    public function __construct(Shop $shop)
    {
        parent::__construct($shop);
        $this->apiGateway = config('platforms.pinduoduo.api.gateway');
    }

    public function getPlatformName(): string
    {
        return 'pinduoduo';
    }

    public function testAuthorization(): bool
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->post($this->apiGateway, [
                'type'         => 'pdd.mall.info.get',
                'client_id'    => $this->shop->app_key,
                'access_token' => $this->shop->access_token,
                'timestamp'    => now()->timestamp,
                'data_type'    => 'JSON',
            ]);

            $result = $response->json();
            $success = !isset($result['error_response']);

            $this->logApiCall(
                'pdd.mall.info.get',
                $this->apiGateway,
                [],
                $result,
                $success ? 'success' : 'failed',
                $result['error_response']['error_msg'] ?? null,
                (int)((microtime(true) - $startTime) * 1000)
            );

            $this->shop->update(['auth_status' => $success ? 1 : 2]);
            return $success;
        } catch (\Exception $e) {
            $this->logApiCall(
                'pdd.mall.info.get',
                $this->apiGateway,
                [],
                [],
                'failed',
                $e->getMessage(),
                (int)((microtime(true) - $startTime) * 1000)
            );
            $this->shop->update(['auth_status' => 2]);
            return false;
        }
    }

    public function refreshAccessToken(): bool
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->post(config('platforms.pinduoduo.oauth.token_url'), [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->shop->app_key,
                'client_secret' => $this->shop->app_secret,
                'refresh_token' => $this->shop->refresh_token,
            ]);

            $result = $response->json();

            if (!empty($result['access_token'])) {
                $this->shop->updateCredentials([
                    'access_token'     => $result['access_token'],
                    'refresh_token'    => $result['refresh_token'] ?? $this->shop->refresh_token,
                    'expires_in'       => $result['expires_in'] ?? 86400,
                ]);

                $this->logApiCall(
                    'pdd.token.refresh',
                    config('platforms.pinduoduo.oauth.token_url'),
                    [],
                    $result,
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return true;
            }

            $this->logApiCall(
                'pdd.token.refresh',
                'https://open-api.pinduoduo.com/oauth/token',
                [],
                $result,
                'failed',
                $result['error_msg'] ?? 'Unknown error',
                (int)((microtime(true) - $startTime) * 1000)
            );
            return false;
        } catch (\Exception $e) {
            $this->logApiCall(
                'pdd.token.refresh',
                'https://open-api.pinduoduo.com/oauth/token',
                [],
                [],
                'failed',
                $e->getMessage(),
                (int)((microtime(true) - $startTime) * 1000)
            );
            return false;
        }
    }

    public function fetchOrders(array $params = []): array
    {
        $startTime = microtime(true);
        $orders = [];

        try {
            $response = Http::timeout(30)->retry(3, 200)->post($this->apiGateway, [
                'type'         => 'pdd.order.list.get',
                'client_id'    => $this->shop->app_key,
                'access_token' => $this->shop->access_token,
                'timestamp'    => now()->timestamp,
                'data_type'    => 'JSON',
                'order_status' => 1, // 待发货
                'start_updated_at' => strtotime(($params['start_time'] ?? now()->subMinutes(10))->format('Y-m-d H:i:s')),
                'end_updated_at'   => strtotime(($params['end_time'] ?? now())->format('Y-m-d H:i:s')),
                'page'         => $params['page'] ?? 1,
                'page_size'    => $params['page_size'] ?? 100,
            ]);

            $result = $response->json();

            if (isset($result['error_response'])) {
                $this->logApiCall(
                    'pdd.order.list.get',
                    $this->apiGateway,
                    $params,
                    $result,
                    'failed',
                    $result['error_response']['error_msg'] ?? 'Unknown error',
                    (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            $orderList = $result['order_list_get_response']['order_list'] ?? [];

            if (empty($orderList)) {
                $this->logApiCall(
                    'pdd.order.list.get',
                    $this->apiGateway,
                    $params,
                    ['count' => 0],
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            foreach ($orderList as $order) {
                $orders[] = [
                    'platform_order_no'  => $order['order_sn'] ?? '',
                    'platform'           => $this->platform,
                    'shop_id'            => $this->shop->id,
                    'order_status'       => $this->mapOrderStatus($order['order_status'] ?? 0),
                    'pay_status'         => ($order['order_status'] ?? 0) >= 1 ? 'paid' : 'unpaid',
                    'total_amount'       => $order['goods_amount'] ?? 0,
                    'pay_amount'         => $order['order_amount'] ?? 0,
                    'buyer_nick'         => '',
                    'receiver_name'      => $order['receiver_name'] ?? '',
                    'receiver_phone'     => $order['receiver_phone'] ?? '',
                    'receiver_province'  => $order['province'] ?? '',
                    'receiver_city'      => $order['city'] ?? '',
                    'receiver_district'  => $order['district'] ?? '',
                    'receiver_address'   => $order['address'] ?? '',
                    'order_created_at'   => (($order['confirm_time'] ?? 0) > 0) ? date('Y-m-d H:i:s', $order['confirm_time']) : null,
                    'pay_time'           => (($order['confirm_time'] ?? 0) > 0) ? date('Y-m-d H:i:s', $order['confirm_time']) : null,
                    'platform_raw_data'  => $order,
                ];
            }

            $this->logApiCall(
                'pdd.order.list.get',
                $this->apiGateway,
                $params,
                ['count' => count($orders)],
                'success',
                responseTime: (int)((microtime(true) - $startTime) * 1000)
            );

            return $orders;
        } catch (\Exception $e) {
            $this->logApiCall(
                'pdd.order.list.get',
                $this->apiGateway,
                $params,
                [],
                'failed',
                $e->getMessage(),
                (int)((microtime(true) - $startTime) * 1000)
            );
            return [];
        }
    }

    public function pushShipment(array $orderData): array
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->retry(2, 200)->post($this->apiGateway, [
                'type'         => 'pdd.logistics.online.send',
                'client_id'    => $this->shop->app_key,
                'access_token' => $this->shop->access_token,
                'timestamp'    => now()->timestamp,
                'data_type'    => 'JSON',
                'order_sn'     => $orderData['platform_order_no'] ?? '',
                'tracking_number' => $orderData['tracking_number'] ?? '',
                'logistics_id' => $orderData['company_code'] ?? 0,
            ]);

            $result = $response->json();
            $success = !isset($result['error_response']);

            $this->logApiCall(
                'pdd.logistics.online.send',
                $this->apiGateway,
                $orderData,
                $result,
                $success ? 'success' : 'failed',
                $result['error_response']['error_msg'] ?? null,
                (int)((microtime(true) - $startTime) * 1000)
            );

            return ['success' => $success, 'error' => $success ? null : ($result['error_response']['error_msg'] ?? 'Unknown error')];
        } catch (\Exception $e) {
            $this->logApiCall(
                'pdd.logistics.online.send',
                $this->apiGateway,
                $orderData,
                [],
                'failed',
                $e->getMessage(),
                (int)((microtime(true) - $startTime) * 1000)
            );
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function syncStock(array $goodsData): array
    {
        $startTime = microtime(true);
        $results = [];

        foreach ($goodsData as $item) {
            try {
                $response = Http::timeout(30)->retry(2, 200)->post($this->apiGateway, [
                    'type'         => 'pdd.goods.quantity.update',
                    'client_id'    => $this->shop->app_key,
                    'access_token' => $this->shop->access_token,
                    'timestamp'    => now()->timestamp,
                    'data_type'    => 'JSON',
                    'goods_id'     => $item['platform_goods_id'] ?? 0,
                    'quantity'     => $item['quantity'] ?? 0,
                    'sku_id'       => $item['platform_sku_id'] ?? 0,
                    'outer_id'     => $item['sku'] ?? '',
                ]);

                $result = $response->json();
                $success = !isset($result['error_response']);

                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => $success,
                    'error'           => $success ? null : ($result['error_response']['error_msg'] ?? 'Unknown error'),
                ];

                usleep(50000);
            } catch (\Exception $e) {
                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => false,
                    'error'           => $e->getMessage(),
                ];
            }
        }

        $this->logApiCall(
            'pdd.goods.quantity.update',
            $this->apiGateway,
            ['count' => count($goodsData)],
            ['results' => count($results)],
            'success',
            responseTime: (int)((microtime(true) - $startTime) * 1000)
        );

        return ['success' => true, 'results' => $results];
    }

    public function publishGoods(array $goodsData): array
    {
        $startTime = microtime(true);
        $results = [];

        foreach ($goodsData as $item) {
            try {
                $response = Http::timeout(30)->retry(2, 200)->post($this->apiGateway, [
                    'type'         => 'pdd.goods.add',
                    'client_id'    => $this->shop->app_key,
                    'access_token' => $this->shop->access_token,
                    'timestamp'    => now()->timestamp,
                    'data_type'    => 'JSON',
                    'goods_name'   => $item['title'] ?? $item['name'] ?? '',
                    'goods_desc'   => $item['description'] ?? '',
                    'cat_id'       => $item['category_id'] ?? 0,
                    'market_price' => $item['price'] ?? 0,
                    'is_refundable'=> true,
                    'is_pre_sale'  => false,
                    'goods_type'   => 1,
                ]);

                $result = $response->json();
                $success = !isset($result['error_response']);

                $results[] = [
                    'system_goods_id'   => $item['id'] ?? null,
                    'success'           => $success,
                    'platform_goods_id' => $success ? ($result['goods_add_response']['goods_id'] ?? '') : '',
                    'error'             => $success ? null : ($result['error_response']['error_msg'] ?? 'Unknown error'),
                ];

                usleep(100000);
            } catch (\Exception $e) {
                $results[] = [
                    'system_goods_id' => $item['id'] ?? null,
                    'success'         => false,
                    'error'           => $e->getMessage(),
                ];
            }
        }

        $this->logApiCall(
            'pdd.goods.add',
            $this->apiGateway,
            ['count' => count($goodsData)],
            ['results' => count($results)],
            'success',
            responseTime: (int)((microtime(true) - $startTime) * 1000)
        );

        return ['success' => true, 'results' => $results];
    }

    private function mapOrderStatus(int $pddStatus): string
    {
        return match ($pddStatus) {
            1       => 'paid',
            2       => 'shipped',
            3       => 'completed',
            4, 5    => 'cancelled',
            default => 'pending',
        };
    }
}
