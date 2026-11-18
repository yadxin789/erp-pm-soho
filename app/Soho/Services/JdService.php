<?php

namespace App\Soho\Services;

use Illuminate\Support\Facades\Http;

class JdService extends BasePlatformService
{
    protected string $apiGateway;

    public function __construct(Shop $shop)
    {
        parent::__construct($shop);
        $this->apiGateway = config('platforms.jd.api.gateway');
    }

    public function getPlatformName(): string
    {
        return 'jd';
    }

    public function testAuthorization(): bool
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->post($this->apiGateway, [
                'method'     => '360buy.shop.get',
                'app_key'    => $this->shop->app_key,
                'access_token' => $this->shop->access_token,
                'timestamp'  => now()->format('Y-m-d H:i:s'),
                'format'     => 'json',
                'v'          => '2.0',
            ]);

            $result = $response->json();
            $success = ($result['code'] ?? '') === '0';

            $this->logApiCall(
                '360buy.shop.get',
                $this->apiGateway,
                [],
                $result,
                $success ? 'success' : 'failed',
                $result['zh_desc'] ?? null,
                (int)((microtime(true) - $startTime) * 1000)
            );

            $this->shop->update(['auth_status' => $success ? 1 : 2]);
            return $success;
        } catch (\Exception $e) {
            $this->logApiCall(
                '360buy.shop.get',
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
            $response = Http::timeout(30)->post(config('platforms.jd.oauth.token_url'), [
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
                    'jd.token.refresh',
                    config('platforms.jd.oauth.token_url'),
                    [],
                    $result,
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return true;
            }

            $this->logApiCall(
                'jd.token.refresh',
                'https://oauth.jd.com/oauth/token',
                [],
                $result,
                'failed',
                $result['error_description'] ?? 'Unknown error',
                (int)((microtime(true) - $startTime) * 1000)
            );
            return false;
        } catch (\Exception $e) {
            $this->logApiCall(
                'jd.token.refresh',
                'https://oauth.jd.com/oauth/token',
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
                'method'       => '360buy.order.search',
                'app_key'      => $this->shop->app_key,
                'access_token' => $this->shop->access_token,
                'timestamp'    => now()->format('Y-m-d H:i:s'),
                'format'       => 'json',
                'v'            => '2.0',
                'start_date'   => ($params['start_time'] ?? now()->subMinutes(10))->format('Y-m-d H:i:s'),
                'end_date'     => ($params['end_time'] ?? now())->format('Y-m-d H:i:s'),
                'page'         => $params['page'] ?? 1,
                'page_size'    => $params['page_size'] ?? 100,
                'order_state'  => 'WAIT_SELLER_STOCK_OUT',
            ]);

            $result = $response->json();

            if (($result['code'] ?? '') !== '0') {
                $this->logApiCall(
                    '360buy.order.search',
                    $this->apiGateway,
                    $params,
                    $result,
                    'failed',
                    $result['zh_desc'] ?? 'Unknown error',
                    (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            $orderList = $result['order_search_response']['order_info_list']['order_info'] ?? [];

            if (empty($orderList)) {
                $this->logApiCall(
                    '360buy.order.search',
                    $this->apiGateway,
                    $params,
                    ['count' => 0],
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            if (isset($orderList['order_id'])) {
                $orderList = [$orderList];
            }

            foreach ($orderList as $order) {
                $orders[] = [
                    'platform_order_no' => $order['order_id'] ?? '',
                    'platform'          => $this->platform,
                    'shop_id'           => $this->shop->id,
                    'order_status'      => $this->mapOrderStatus($order['order_state'] ?? ''),
                    'pay_status'        => 'paid',
                    'total_amount'      => $order['order_total_price'] ?? 0,
                    'pay_amount'        => $order['order_payment'] ?? 0,
                    'buyer_nick'        => '',
                    'receiver_name'     => $order['consignee_info']['fullname'] ?? '',
                    'receiver_phone'    => $order['consignee_info']['mobile'] ?? $order['consignee_info']['telephone'] ?? '',
                    'receiver_province' => $order['consignee_info']['province'] ?? '',
                    'receiver_city'     => $order['consignee_info']['city'] ?? '',
                    'receiver_district' => $order['consignee_info']['county'] ?? '',
                    'receiver_address'  => $order['consignee_info']['full_address'] ?? '',
                    'order_created_at'  => $order['order_start_time'] ?? null,
                    'pay_time'          => $order['order_payment_time'] ?? null,
                    'platform_raw_data' => $order,
                ];
            }

            $this->logApiCall(
                '360buy.order.search',
                $this->apiGateway,
                $params,
                ['count' => count($orders)],
                'success',
                responseTime: (int)((microtime(true) - $startTime) * 1000)
            );

            return $orders;
        } catch (\Exception $e) {
            $this->logApiCall(
                '360buy.order.search',
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
                'method'        => '360buy.order.sop.outstorage',
                'app_key'       => $this->shop->app_key,
                'access_token'  => $this->shop->access_token,
                'timestamp'     => now()->format('Y-m-d H:i:s'),
                'format'        => 'json',
                'v'             => '2.0',
                'order_id'      => $orderData['platform_order_no'] ?? '',
                'logistics_id'  => $orderData['tracking_number'] ?? '',
                'waybill'       => $orderData['tracking_number'] ?? '',
                'trade_no'      => $orderData['company_code'] ?? '',
            ]);

            $result = $response->json();
            $success = ($result['code'] ?? '') === '0';

            $this->logApiCall(
                '360buy.order.sop.outstorage',
                $this->apiGateway,
                $orderData,
                $result,
                $success ? 'success' : 'failed',
                $success ? null : ($result['zh_desc'] ?? 'Unknown error'),
                (int)((microtime(true) - $startTime) * 1000)
            );

            return ['success' => $success, 'error' => $success ? null : ($result['zh_desc'] ?? 'Unknown error')];
        } catch (\Exception $e) {
            $this->logApiCall(
                '360buy.order.sop.outstorage',
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
                    'method'       => '360buy.ware.sku.update',
                    'app_key'      => $this->shop->app_key,
                    'access_token' => $this->shop->access_token,
                    'timestamp'    => now()->format('Y-m-d H:i:s'),
                    'format'       => 'json',
                    'v'            => '2.0',
                    'sku_id'       => $item['platform_goods_id'] ?? '',
                    'stock_num'    => $item['quantity'] ?? 0,
                ]);

                $result = $response->json();
                $success = ($result['code'] ?? '') === '0';

                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => $success,
                    'error'           => $success ? null : ($result['zh_desc'] ?? 'Unknown error'),
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
            '360buy.ware.sku.update',
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
                    'method'       => '360buy.ware.add',
                    'app_key'      => $this->shop->app_key,
                    'access_token' => $this->shop->access_token,
                    'timestamp'    => now()->format('Y-m-d H:i:s'),
                    'format'       => 'json',
                    'v'            => '2.0',
                    'title'        => $item['title'] ?? $item['name'] ?? '',
                    'cid'          => $item['category_id'] ?? 0,
                    'jd_price'     => $item['price'] ?? 0,
                    'stock_num'    => $item['stock'] ?? 999,
                    'item_num'     => $item['sku'] ?? '',
                ]);

                $result = $response->json();
                $success = ($result['code'] ?? '') === '0';

                $results[] = [
                    'system_goods_id'   => $item['id'] ?? null,
                    'success'           => $success,
                    'platform_goods_id' => $success ? ($result['ware_add_response']['ware_id'] ?? '') : '',
                    'error'             => $success ? null : ($result['zh_desc'] ?? 'Unknown error'),
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
            '360buy.ware.add',
            $this->apiGateway,
            ['count' => count($goodsData)],
            ['results' => count($results)],
            'success',
            responseTime: (int)((microtime(true) - $startTime) * 1000)
        );

        return ['success' => true, 'results' => $results];
    }

    private function mapOrderStatus(string $jdStatus): string
    {
        return match ($jdStatus) {
            'WAIT_SELLER_STOCK_OUT'  => 'paid',
            'WAIT_GOODS_RECEIVE_CONFIRM' => 'shipped',
            'FINISHED_L'             => 'completed',
            'TRADE_CANCEL'           => 'cancelled',
            default                  => 'pending',
        };
    }
}
