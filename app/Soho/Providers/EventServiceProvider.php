<?php

namespace App\Soho\Providers;

use App\Soho\Events\OrderStatusChanged;
use App\Soho\Events\ShipmentCreated;
use App\Soho\Events\StockChanged;
use App\Soho\Listeners\LoginListener;
use App\Soho\Listeners\OrderStatusChangedListener;
use App\Soho\Listeners\ShipmentCreatedListener;
use App\Soho\Listeners\StockChangedListener;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * 事件服务提供者
 * 注册所有事件和监听器的绑定关系
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件与监听器映射
     *
     * @var array
     */
    protected $listen = [
        // 订单状态变更事件
        OrderStatusChanged::class => [
            OrderStatusChangedListener::class,
        ],

        // 发货记录创建事件
        ShipmentCreated::class => [
            ShipmentCreatedListener::class,
        ],

        // 库存变更事件
        StockChanged::class => [
            StockChangedListener::class,
        ],

        // 用户登录成功事件
        Login::class => [
            [LoginListener::class, 'handleLogin'],
        ],

        // 用户登录失败事件
        Failed::class => [
            [LoginListener::class, 'handleFailed'],
        ],

        // 用户登出事件
        Logout::class => [
            [LoginListener::class, 'handleLogout'],
        ],
    ];

    /**
     * 启动事件服务
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * 是否自动发现事件
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}