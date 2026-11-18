<?php

namespace App\Soho\Services;

use Illuminate\Support\Facades\Http;

class AmazonService extends BasePlatformService
{
    protected string $apiGateway;

    public function __construct(Shop $shop)
    {
        parent::__construct($shop);
        $this->apiGateway = config('platforms.amazon.api.gateway');
    }

    public function getPlatformName(): string
    {
        return 'amazon';
    }

    public function testAuthorization(): bool
    {
        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-amz-access-token' => $this->shop->access_token,
                ])
                ->get($this->apiGateway . '/sellers/v1/marketplaceParticipations');

            $success = $response->successful();

            $this->logApiCall(
                'amazon.getMarketplaceParticipations',
                $this->apiGateway . '/sellers/v1/marketplaceParticipations',
                [],
                $response->json(),
                $success ? 'success' : 'failed',
                $success ? null : ($response->body()),
                (int)((microtime(true) - $startTime) * 1000)
            );

            $this->shop->update(['auth_status' => $success ? 1 : 2]);
            return $success;
        } catch (\Exception $e) {
            $this->logApiCall(
                'amazon.getMarketplaceParticipations',
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
            $response = Http::timeout(30)
                ->asForm()
                ->post(config('platforms.amazon.oauth.token_url'), [
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
                    'expires_in'       => $result['expires_in'] ?? 3600,
                ]);

                $this->logApiCall(
                    'amazon.auth.refresh',
                    config('platforms.amazon.oauth.token_url'),
                    [],
                    [],
                    'success',
                    responseTime: (int)((microtime(true) - $startTime) * 1000)
                );
                return true;
            }

            $this->logApiCall(
                'amazon.auth.refresh',
                'https://api.amazon.com/auth/o2/token',
                [],
                $result,
                'failed',
                $result['error_description'] ?? 'Unknown error',
                (int)((microtime(true) - $startTime) * 1000)
            );
            return false;
        } catch (\Exception $e) {
            $this->logApiCall(
                'amazon.auth.refresh',
                'https://api.amazon.com/auth/o2/token',
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
            $startDate = ($params['start_time'] ?? now()->subMinutes(10))->format('Y-m-d\TH:i:s\Z');
            $endDate = ($params['end_time'] ?? now())->format('Y-m-d\TH:i:s\Z');
            // 修正：使用 platform_config 字段读取 marketplace_id
            $marketplaceId = $this->shop->platform_config['marketplace_id'] ?? config('platforms.amazon.marketplaces.US.id', 'ATVPDKIKX0DER');

            $response = Http::timeout(30)->retry(3, 200)
                ->withHeaders([
                    'x-amz-access-token' => $this->shop->access_token,
                ])
                ->get($this->apiGateway . '/orders/v0/orders', [
                    'MarketplaceIds'   => $marketplaceId,
                    'CreatedAfter'     => $startDate,
                    'CreatedBefore'    => $endDate,
                    'OrderStatuses'    => 'Unshipped,PartiallyShipped',
                    'FulfillmentChannels' => 'MFN',
                ]);

            $result = $response->json();

            if (!$response->successful()) {
                $this->logApiCall(
                    'amazon.getOrders',
                    $this->apiGateway . '/orders/v0/orders',
                    $params,
                    $result,
                    'failed',
                    $result['errors'][0]['message'] ?? 'Unknown error',
                    (int)((microtime(true) - $startTime) * 1000)
                );
                return [];
            }

            $orderList = $result['Orders'] ?? [];

            if (empty($orderList)) {
                $this->logApiCall(
                    'amazon.getOrders',
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
                    'platform_order_no'  => $order['AmazonOrderId'] ?? '',
                    'platform'           => $this->platform,
                    'shop_id'            => $this->shop->id,
                    'order_status'       => $this->mapOrderStatus($order['OrderStatus'] ?? 'Pending'),
                    'pay_status'         => ($order['OrderStatus'] ?? '') !== 'Pending' ? 'paid' : 'unpaid',
                    'total_amount'       => $order['OrderTotal']['Amount'] ?? 0,
                    'pay_amount'         => $order['OrderTotal']['Amount'] ?? 0,
                    'currency'           => $order['OrderTotal']['CurrencyCode'] ?? 'USD',
                    'buyer_nick'         => $order['BuyerEmail'] ?? '',
                    'receiver_name'      => $order['ShippingAddress']['Name'] ?? '',
                    'receiver_phone'     => $order['ShippingAddress']['Phone'] ?? '',
                    'receiver_address'   => implode(', ', array_filter([
                        $order['ShippingAddress']['AddressLine1'] ?? '',
                        $order['ShippingAddress']['AddressLine2'] ?? '',
                        $order['ShippingAddress']['City'] ?? '',
                        $order['ShippingAddress']['StateOrRegion'] ?? '',
                        $order['ShippingAddress']['PostalCode'] ?? '',
                        $order['ShippingAddress']['CountryCode'] ?? '',
                    ])),
                    'order_created_at'   => $order['PurchaseDate'] ?? null,
                    'platform_raw_data'  => $order,
                ];
            }

            $this->logApiCall(
                'amazon.getOrders',
                $this->apiGateway,
                $params,
                ['count' => count($orders)],
                'success',
                responseTime: (int)((microtime(true) - $startTime) * 1000)
            );

            return $orders;
        } catch (\Exception $e) {
            $this->logApiCall(
                'amazon.getOrders',
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
            $orderId = $orderData['platform_order_no'] ?? '';
            $fulfillmentData = [
                'fulfillmentDate' => now()->format('Y-m-d\TH:i:s\Z'),
                'trackingNumber'  => $orderData['tracking_number'] ?? '',
                'carrierName'     => $orderData['company_name'] ?? $orderData['company_code'] ?? 'OTHER',
                'shippingMethod'  => $orderData['shipping_method'] ?? 'Standard',
                'orderItems'      => [
                    [
                        'orderItemId' => $orderData['order_item_id'] ?? $orderId,
                        'quantity'    => 1,
                    ],
                ],
            ];

            $response = Http::timeout(30)->retry(2, 200)
                ->withHeaders([
                    'x-amz-access-token' => $this->shop->access_token,
                ])
                ->post($this->apiGateway . '/fulfillment/v0/orders/' . $orderId . '/fulfillment', [
                    'fulfillment' => $fulfillmentData,
                ]);

            $result = $response->json();
            $success = $response->successful();

            $this->logApiCall(
                'amazon.fulfillmentOrder',
                $this->apiGateway . '/fulfillment/v0/orders/' . $orderId . '/fulfillment',
                $orderData,
                $result,
                $success ? 'success' : 'failed',
                $result['errors'][0]['message'] ?? null,
                (int)((microtime(true) - $startTime) * 1000)
            );

            return ['success' => $success, 'error' => $success ? null : ($result['errors'][0]['message'] ?? 'Unknown error')];
        } catch (\Exception $e) {
            $this->logApiCall(
                'amazon.fulfillmentOrder',
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
                $sku = $item['sku'] ?? $item['platform_sku_id'] ?? '';
                $qty = $item['quantity'] ?? 0;

                $response = Http::timeout(30)->retry(2, 200)
                    ->withHeaders([
                        'x-amz-access-token' => $this->shop->access_token,
                    ])
                    // 修正：使用 platform_config 字段读取 marketplace_id
                    ->put($this->apiGateway . '/listings/2021-08-01/items/' . $sku . '/offers/' . ($this->shop->platform_config['marketplace_id'] ?? config('platforms.amazon.marketplaces.US.id', 'ATVPDKIKX0DER')), [
                        'quantity' => max(0, $qty),
                    ]);

                $success = $response->successful();

                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => $success,
                    'error'           => $success ? null : ($response->body()),
                ];

                usleep(100000);
            } catch (\Exception $e) {
                $results[] = [
                    'system_goods_id' => $item['system_goods_id'] ?? null,
                    'success'         => false,
                    'error'           => $e->getMessage(),
                ];
            }
        }

        $this->logApiCall(
            'amazon.updateInventory',
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
                $sku = $item['sku'] ?? '';

                $response = Http::timeout(30)->retry(2, 200)
                    ->withHeaders([
                        'x-amz-access-token' => $this->shop->access_token,
                    ])
                    ->put($this->apiGateway . '/listings/2021-08-01/items/' . $sku, [
                        'productType'    => $item['category_name'] ?? 'PRODUCT',
                        'requirements'   => 'LISTING',
                        'attributes'     => [
                            'item_name'  => [
                                ['value' => $item['title'] ?? $item['name'] ?? ''],
                            ],
                            'list_price' => [
                                ['value' => (float)($item['price'] ?? 0), 'currency' => 'USD'],
                            ],
                            'product_description' => [
                                ['value' => $item['description'] ?? ''],
                            ],
                        ],
                    ]);

                $result = $response->json();
                $success = $response->successful();

                $results[] = [
                    'system_goods_id'   => $item['id'] ?? null,
                    'success'           => $success,
                    'platform_goods_id' => $success ? $sku : '',
                    'error'             => $success ? null : ($result['errors'][0]['message'] ?? 'Unknown error'),
                ];

                usleep(200000);
            } catch (\Exception $e) {
                $results[] = [
                    'system_goods_id' => $item['id'] ?? null,
                    'success'         => false,
                    'error'           => $e->getMessage(),
                ];
            }
        }

        $this->logApiCall(
            'amazon.listingPublication',
            $this->apiGateway,
            ['count' => count($goodsData)],
            ['results' => count($results)],
            'success',
            responseTime: (int)((microtime(true) - $startTime) * 1000)
        );

        return ['success' => true, 'results' => $results];
    }

    private function mapOrderStatus(string $amazonStatus): string
    {
        return match ($amazonStatus) {
            'Pending', 'PendingAvailability' => 'pending',
            'Unshipped', 'PartiallyShipped' => 'paid',
            'Shipped'                        => 'shipped',
            'Canceled'                       => 'cancelled',
            'InvoiceUnconfirmed'             => 'completed',
            default                          => 'pending',
        };
    }
}
