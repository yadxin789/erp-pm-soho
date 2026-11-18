<?php

namespace App\Soho\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            // 配置应用名称和徽标
            ->brandName('多平台电商店铺统一管理系统')
            ->brandLogo(asset('images/logo.svg'))
            // 配置颜色主题 - 使用参考项目的配色
            ->colors([
                'primary' => Color::hex('#667eea'),
                'success' => Color::hex('#28a745'),
                'danger' => Color::hex('#dc3545'),
                'warning' => Color::hex('#ffc107'),
                'info' => Color::hex('#17a2b8'),
            ])
            // 配置字体
            ->font('Microsoft YaHei')
            // 配置侧边栏宽度 - 参考项目风格
            ->sidebarWidth('256px')
            // 配置导航组标签（中文）
            ->navigationGroups([
                NavigationGroup::make('运营总览'),
                NavigationGroup::make('组织权限'),
                NavigationGroup::make('店铺管理'),
                NavigationGroup::make('商品中心'),
                NavigationGroup::make('订单管理'),
                NavigationGroup::make('仓储中心'),
                NavigationGroup::make('售后管理'),
                NavigationGroup::make('库存同步'),
                NavigationGroup::make('数据报表'),
                NavigationGroup::make('系统运维'),
            ])
            // 注册页面
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\SalesReport::class,
                \App\Filament\Pages\StockOverview::class,
                \App\Filament\Pages\ProductRanking::class,
                \App\Filament\Pages\InventoryReport::class,
                \App\Filament\Pages\OrderTimelinessReport::class,
                \App\Filament\Pages\OperationLogs::class,
                \App\Filament\Pages\SystemSettings::class,
            ])
            // 注册资源
            ->resources([
                \App\Filament\Resources\UserResource::class,
                \App\Filament\Resources\DepartmentResource::class,
                \App\Filament\Resources\RoleResource::class,
                \App\Filament\Resources\ShopResource::class,
                \App\Filament\Resources\SystemGoodsResource::class,
                \App\Filament\Resources\PlatformSkuResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\BrandResource::class,
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\WarehouseResource::class,
                \App\Filament\Resources\InventoryResource::class,
                \App\Filament\Resources\PlatformOrderResource::class,
                \App\Filament\Resources\OrderAfterSaleResource::class,
                \App\Filament\Resources\ExpressCompanyResource::class,
                \App\Filament\Resources\PlatformApiLogResource::class,
                \App\Filament\Resources\StockSyncLogResource::class,
                \App\Filament\Resources\SyncTaskLogResource::class,
            ])
            // 注册中间件
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}