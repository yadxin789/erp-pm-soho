<?php

namespace App\Soho\Services;

use App\Soho\Models\PlatformOrder;
use Illuminate\Support\Facades\Http;

class TaobaoService extends BasePlatformService
{
    protected string $apiGateway;

    public function __construct(Shop $shop)
    {
        parent::__construct($shop);
        $this->apiGateway = config('platforms.taobao.api.gateway');
    }

    public function getPlatformName(): string
    {
        return 'taobao';
    }

    public function testAuthorization(): bool
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->post($this->apiGateway, [
                'method'      => 'taobao.shop.get',
                'app_key'     => $this->shop->app_key,
                'session'     => $this->shop->access_token,
                'timestamp'   => now()->format('Y-m-d H:i:s'),
                'format'      => 'json',
                'v'           => '2.0',
                'sign_method' => 'md5',
            ]);

            $result = $response->json();
            $success = !isset($result['error_response']);

            $this->logApiCall(
                'taobao.shop.get',
                $this->apiGateway,
                ['method' => 'taobao.shop.get'],
                $result,
                $success ? 'success' : 'failed',
                $result['error_response']['sub_msg'] ?? null,
                (int)((microtime(true) - $startTime) * 1000)
            );

            $this->shop->update(['auth_status' => $success ? 1 : 2]);
            return $success;
        } catch (\Exception $e) {
            $this->logApiCall(
                'taobao.shop.get',
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
            $response = Http::timeout(30)->post(config('platforms.taobao.oauth.token_url'), [
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
                    'taobao.top.auth.token.refresh',
                    config('platforms.taobao.oauth.token_url'),
                    ['grant_type' => 'refresh_token'],
                    $result,
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return true;
            }

            $this->logApiCall(
                'taobao.top.auth.token.refresh',
                'https://oauth.taobao.com/token',
                [],
                $result,
                'failed',
                $result['error_description'] ?? $result['error'] ?? 'Unknown error',
                (int)((microtime(true) - $startTime) * 1000)
            );
            return false;
        } catch (\Exception $e) {
            $this->logApiCall(
                'taobao.top.auth.token.refresh',
                'https://oauth.taobao.com/token',
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
        $pageNo = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 100;

        try {
            $response = Http::timeout(30)->retry(3, 200)->post($this->apiGateway, [
                'method'        => 'taobao.trades.sold.get',
                'app_key'       => $this->shop->app_key,
                'session'       => $this->shop->access_token,
                'timestamp'     => now()->format('Y-m-d H:i:s'),
                'format'        => 'json',
                'v'             => '2.0',
                'sign_method'   => 'md5',
                'fields'        => 'tid,status,payment,total_fee,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_mobile,created,pay_time,consign_time,buyer_nick,buyer_message',
                'start_created' => ($params['start_time'] ?? now()->subMinutes(10))->format('Y-m-d H:i:s'),
                'end_created'   => ($params['end_time'] ?? now())->format('Y-m-d H:i:s'),
                'page_no'       => $pageNo,
                'page_size'     => $pageSize,
                'use_has_next'  => 'true',
            ]);

            $result = $response->json();

            if (isset($result['error_response'])) {
                $this->logApiCall(
                    'taobao.trades.sold.get',
                    $this->apiGateway,
                    $params,
                    $result,
                    'failed',
                    $result['error_response']['sub_msg'] ?? 'Unknown error',
                    (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            $trades = $result['trades_sold_get_response']['trades']['trade'] ?? [];

            if (empty($trades)) {
                $this->logApiCall(
                    'taobao.trades.sold.get',
                    $this->apiGateway,
                    $params,
                    ['count' => 0],
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            // 单条记录时 API 可能返回关联数组而非索引数组
            if (isset($trades['tid'])) {
                $trades = [$trades];
            }

            foreach ($trades as $trade) {
                $orders[] = [
                    'platform_order_no' => $trade['tid'] ?? '',
                    'platform'          => $this->platform,
                    'shop_id'           => $this->shop->id,
                    'order_status'      => $this->mapOrderStatus($trade['status'] ?? ''),
                    'pay_status'        => ($trade['status'] ?? '') === 'WAIT_BUYER_PAY' ? 'unpaid' : 'paid',
                    'total_amount'      => $trade['total_fee'] ?? 0,
                    'pay_amount'        => $trade['payment'] ?? 0,
                    'buyer_nick'        => $trade['buyer_nick'] ?? '',
                    'buyer_message'     => $trade['buyer_message'] ?? '',
                    'receiver_name'     => $trade['receiver_name'] ?? '',
                    'receiver_phone'    => $trade['receiver_mobile'] ?? '',
                    'receiver_state'    => $trade['receiver_state'] ?? '',
                    'receiver_city'     => $trade['receiver_city'] ?? '',
                    'receiver_district' => $trade['receiver_district'] ?? '',
                    'receiver_address'  => $trade['receiver_address'] ?? '',
                    'order_created_at'  => $trade['created'] ?? null,
                    'pay_time'          => $trade['pay_time'] ?? null,
                    'ship_time'         => $trade['consign_time'] ?? null,
                    'platform_raw_data' => $trade,
                ];
            }

            $this->logApiCall(
                'taobao.trades.sold.get',
                $this->apiGateway,
                $params,
                ['count' => count($orders)],
                'success',
                responseTime: (int)((microtime(true) - $startTime) * 1000)
            );

            return $orders;
        } catch (\Exception $e) {
            $this->logApiCall(
                'taobao.trades.sold.get',
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
                'method'       => 'taobao.logistics.offline.send',
                'app_key'      => $this->shop->app_key,
                'session'      => $this->shop->access_token,
                'timestamp'    => now()->format('Y-m-d H:i:s'),
                'format'       => 'json',
                'v'            => '2.0',
                'sign_method'  => 'md5',
                'tid'          => $orderData['platform_order_no'] ?? '',
                'out_sid'      => $orderData['tracking_number'] ?? '',
                'company_code' => $orderData['company_code'] ?? '',
            ]);

            $result = $response->json();

            if (isset($result['error_response'])) {
                $this->logApiCall(
                    'taobao.logistics.offline.send',
                    $this->apiGateway,
                    $orderData,
                    $result,
                    'failed',
                    $result['error_response']['sub_msg'] ?? 'Unknown error',
                    (int)((microtime(true) - $startTime) * 1000)
                );
                return ['success' => false, 'error' => $result['error_response']['sub_msg'] ?? 'Unknown error'];
            }

            $isSuccess = $result['logistics_offline_send_response']['shipping']['is_success'] ?? false;

            $this->logApiCall(
                'taobao.logistics.offline.send',
                $this->apiGateway,
                $orderData,
                $result,
                $isSuccess ? 'success' : 'failed',
                responseTime: (int)((microtime(true) - $startTime) * 1000)
            );

            return ['success' => $isSuccess];
        } catch (\Exception $e) {
            $this->logApiCall(
                'taobao.logistics.offline.send',
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
                $response = Http::timeout(30)->retry(2, 100)->post($this->apiGateway, [
                    'method'      => 'taobao.item.quantity.update',
                    'app_key'     => $this->shop->app_key,
                    'session'     => $this->shop->access_token,
                    'timestamp'   => now()->format('Y-m-d H:i:s'),
                    'format'      => 'json',
                    'v'           => '2.0',
                    'sign_method' => 'md5',
                    'num_iid'     => $item['platform_goods_id'] ?? '',
                    'quantity'    => $item['quantity'] ?? 0,
                    'type'        => 1,
                ]);

                $result = $response->json();
                $success = !isset($result['error_response']);

                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => $success,
                    'error'           => $success ? null : ($result['error_response']['sub_msg'] ?? 'Unknown error'),
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
            'taobao.item.quantity.update',
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
                    'method'      => 'taobao.item.add',
                    'app_key'     => $this->shop->app_key,
                    'session'     => $this->shop->access_token,
                    'timestamp'   => now()->format('Y-m-d H:i:s'),
                    'format'      => 'json',
                    'v'           => '2.0',
                    'sign_method' => 'md5',
                    'num'         => $item['stock'] ?? 999,
                    'price'       => $item['price'] ?? 0,
                    'title'       => $item['title'] ?? $item['name'] ?? '',
                    'desc'        => $item['description'] ?? '',
                    'cid'         => $item['category_id'] ?? 0,
                    'outer_id'    => $item['sku'] ?? '',
                ]);

                $result = $response->json();
                $success = !isset($result['error_response']);

                $results[] = [
                    'system_goods_id'   => $item['id'] ?? null,
                    'success'           => $success,
                    'platform_goods_id' => $success ? ($result['item_add_response']['item']['num_iid'] ?? '') : '',
                    'error'             => $success ? null : ($result['error_response']['sub_msg'] ?? 'Unknown error'),
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
            'taobao.item.add',
            $this->apiGateway,
            ['count' => count($goodsData)],
            ['results' => count($results)],
            'success',
            responseTime: (int)((microtime(true) - $startTime) * 1000)
        );

        return ['success' => true, 'results' => $results];
    }

    private function mapOrderStatus(string $taobaoStatus): string
    {
        return match ($taobaoStatus) {
            'WAIT_BUYER_PAY'                                   => 'pending',
            'WAIT_SELLER_SEND_GOODS'                           => 'paid',
            'WAIT_BUYER_CONFIRM_GOODS', 'WAIT_GOODS_RECEIVE_CONFIRM' => 'shipped',
            'TRADE_FINISHED'                                   => 'completed',
            'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'           => 'cancelled',
            default                                            => 'pending',
        };
    }
}
