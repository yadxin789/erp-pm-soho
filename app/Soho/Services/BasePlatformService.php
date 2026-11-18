<?php

namespace App\Soho\Services;

use App\Soho\Models\PlatformApiLog;
use App\Soho\Models\Shop;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

abstract class BasePlatformService
{
    protected Client $httpClient;
    protected Shop $shop;
    protected string $platform;

    abstract public function getPlatformName(): string;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
        $this->platform = $shop->platform;
        $apiConfig = config("platforms.{$this->platform}.api", []);
        $this->httpClient = new Client([
            'timeout'  => $apiConfig['timeout'] ?? 30,
            'connect_timeout' => 10,
        ]);
    }

    protected function logApiCall(string $method, string $url, array $params, array $response, string $status, ?string $error = null, int $responseTime = 0): void
    {
        // CLI 环境下 request() 不可用，使用默认值
        $ipAddress = function_exists('request') && request() ? request()->ip() : 'cli';

        PlatformApiLog::create([
            'platform'         => $this->platform,
            'shop_id'          => $this->shop->id,
            'api_method'       => $method,
            'request_url'      => $url,
            'request_params'   => $params,
            'response_data'    => $response,
            'response_status'  => $status,
            'error_message'    => $error,
            'response_time_ms' => $responseTime,
            'trace_id'         => (string) Str::uuid(),
            'ip_address'       => $ipAddress,
        ]);
    }

    abstract public function testAuthorization(): bool;

    abstract public function refreshAccessToken(): bool;

    abstract public function fetchOrders(array $params = []): array;

    abstract public function pushShipment(array $orderData): array;

    abstract public function syncStock(array $goodsData): array;

    abstract public function publishGoods(array $goodsData): array;
}
