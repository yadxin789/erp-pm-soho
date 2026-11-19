<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // 分页口径与 ERP 参考实现一致：Bootstrap 5 结构 + 自定义「共 N 条」视图
        Paginator::useBootstrapFive();
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.custom');
    }
}
