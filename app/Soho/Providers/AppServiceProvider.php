<?php

namespace App\Soho\Providers;

use App\Soho\Models\PlatformOrder;
use App\Soho\Models\SystemGoods;
use App\Soho\Models\SystemShipRecord;
use App\Soho\Observers\PlatformOrderObserver;
use App\Soho\Observers\SystemGoodsObserver;
use App\Soho\Observers\SystemShipRecordObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 使用 Bootstrap 5 分页模板（Laravel 12 默认使用 Tailwind，需显式指定）
        Paginator::useBootstrapFive();

        // 注册模型观察者
        SystemGoods::observe(SystemGoodsObserver::class);
        SystemShipRecord::observe(SystemShipRecordObserver::class);
        PlatformOrder::observe(PlatformOrderObserver::class);
    }
}
