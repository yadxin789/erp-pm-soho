<?php

namespace App\Soho\Services;

use App\Soho\Contracts\PlatformCapabilities;
use App\Soho\Contracts\AuthorizationAdapter;
use App\Soho\Contracts\ProductAdapter;
use App\Soho\Contracts\OrderAdapter;
use App\Soho\Contracts\InventoryAdapter;
use App\Soho\Contracts\ShipmentAdapter;
use App\Soho\Contracts\AfterSaleAdapter;
use App\Soho\Contracts\SettlementAdapter;
use App\Soho\Contracts\WebhookAdapter;
use App\Soho\Models\Shop;
use Illuminate\Contracts\Container\Container;

/**
 * 平台服务工厂类
 * 对应审计报告 V2.1 §16 平台适配器架构
 * 支持按能力域获取专用适配器接口
 */
class PlatformServiceFactory
{
    /**
     * Laravel容器实例
     */
    protected static ?Container $container = null;

    /**
     * 平台与服务类的映射关系
     */
    protected static array $platformMap = [
        'taobao'    => TaobaoService::class,
        'jd'        => JdService::class,
        'pinduoduo' => PinduoduoService::class,
        'amazon'    => AmazonService::class,
    ];

    /**
     * 平台能力声明缓存
     */
    protected static array $capabilitiesCache = [];

    /**
     * 设置容器实例
     */
    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }

    /**
     * 获取容器实例
     */
    protected static function getContainer(): Container
    {
        if (!static::$container) {
            static::$container = app();
        }
        return static::$container;
    }

    /**
     * 根据店铺创建对应的平台服务实例
     */
    public static function create(Shop $shop): BasePlatformService
    {
        $platform = $shop->platform;

        if (!isset(static::$platformMap[$platform])) {
            throw new \InvalidArgumentException("不支持的平台: {$platform}");
        }

        $serviceClass = static::$platformMap[$platform];

        return static::getContainer()->makeWith($serviceClass, ['shop' => $shop]);
    }

    /**
     * 获取授权适配器
     */
    public static function getAuthorizationAdapter(Shop $shop): AuthorizationAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof AuthorizationAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持授权适配器");
        }
        return $service;
    }

    /**
     * 获取商品适配器
     */
    public static function getProductAdapter(Shop $shop): ProductAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof ProductAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持商品适配器");
        }
        return $service;
    }

    /**
     * 获取订单适配器
     */
    public static function getOrderAdapter(Shop $shop): OrderAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof OrderAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持订单适配器");
        }
        return $service;
    }

    /**
     * 获取库存适配器
     */
    public static function getInventoryAdapter(Shop $shop): InventoryAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof InventoryAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持库存适配器");
        }
        return $service;
    }

    /**
     * 获取发货适配器
     */
    public static function getShipmentAdapter(Shop $shop): ShipmentAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof ShipmentAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持发货适配器");
        }
        return $service;
    }

    /**
     * 获取售后适配器
     */
    public static function getAfterSaleAdapter(Shop $shop): AfterSaleAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof AfterSaleAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持售后适配器");
        }
        return $service;
    }

    /**
     * 获取结算适配器
     */
    public static function getSettlementAdapter(Shop $shop): SettlementAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof SettlementAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持结算适配器");
        }
        return $service;
    }

    /**
     * 获取Webhook适配器
     */
    public static function getWebhookAdapter(Shop $shop): WebhookAdapter
    {
        $service = static::create($shop);
        if (!$service instanceof WebhookAdapter) {
            throw new \RuntimeException("平台 {$shop->platform} 不支持Webhook适配器");
        }
        return $service;
    }

    /**
     * 获取平台能力声明
     */
    public static function getCapabilities(Shop $shop): PlatformCapabilities
    {
        $cacheKey = $shop->id . '_' . $shop->platform;

        if (!isset(static::$capabilitiesCache[$cacheKey])) {
            $service = static::create($shop);
            if (!$service instanceof PlatformCapabilities) {
                throw new \RuntimeException("平台 {$shop->platform} 未实现 PlatformCapabilities 接口");
            }
            static::$capabilitiesCache[$cacheKey] = $service;
        }

        return static::$capabilitiesCache[$cacheKey];
    }

    /**
     * 检查平台是否支持某能力
     */
    public static function supportsCapability(Shop $shop, string $capability): bool
    {
        $capabilities = static::getCapabilities($shop);
        return match($capability) {
            'product_pull'    => $capabilities->supportsProductPull(),
            'product_push'    => $capabilities->supportsProductPush(),
            'inventory_push'  => $capabilities->supportsInventoryPush(),
            'order_pull'      => $capabilities->supportsOrderPull(),
            'shipment_push'   => $capabilities->supportsShipmentPush(),
            'aftersale_pull'  => $capabilities->supportsAfterSalePull(),
            'refund_push'     => $capabilities->supportsRefundPush(),
            'settlement_pull' => $capabilities->supportsSettlementPull(),
            'webhook'         => $capabilities->supportsWebhook(),
            default           => false,
        };
    }

    /**
     * 注册自定义平台服务
     */
    public static function register(string $platform, string $serviceClass): void
    {
        if (!is_subclass_of($serviceClass, BasePlatformService::class)) {
            throw new \InvalidArgumentException("服务类必须继承 BasePlatformService");
        }
        static::$platformMap[$platform] = $serviceClass;
        // 清除能力缓存
        static::$capabilitiesCache = [];
    }

    /**
     * 获取所有支持的平台列表
     */
    public static function getSupportedPlatforms(): array
    {
        return array_keys(static::$platformMap);
    }

    /**
     * 清除能力缓存
     */
    public static function clearCapabilitiesCache(): void
    {
        static::$capabilitiesCache = [];
    }
}