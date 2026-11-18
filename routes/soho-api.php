<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SOHO API 路由（TS-17）
|--------------------------------------------------------------------------
| 源 E:\_tmp_soho_baseline\routes\api.php（51 条命名路由），URL 前加 /soho，
| 路由名加 soho.api.，控制器指向 App\Soho\Http\Controllers\Api\*。
*/

Route::middleware('api')->prefix('soho/api')->name('soho.api.')->group(function () {
/**
 * API 版本控制路由组
 * 注意：Laravel 12 自动为 API 路由添加 /api 前缀
 */
Route::prefix('v1')->group(function () {
    /**
     * 认证相关路由（公开）
     */
    Route::prefix('auth')->group(function () {
        Route::get('/callback/{platform}', [\App\Soho\Http\Controllers\Api\Auth\OAuthController::class, 'callback'])->name('auth.callback');
    });

    /**
     * Webhook接收路由（平台推送，通过签名校验而非用户认证）
     */
    Route::prefix('webhooks')->group(function () {
        Route::post('/{platform}/event', [\App\Soho\Http\Controllers\Api\Webhook\WebhookController::class, 'handle'])->name('webhooks.event');
        Route::post('/{platform}/order', [\App\Soho\Http\Controllers\Api\Webhook\WebhookController::class, 'order'])->name('webhooks.order');
        Route::post('/{platform}/inventory', [\App\Soho\Http\Controllers\Api\Webhook\WebhookController::class, 'inventory'])->name('webhooks.inventory');
        Route::post('/{platform}/after-sale', [\App\Soho\Http\Controllers\Api\Webhook\WebhookController::class, 'afterSale'])->name('webhooks.after-sale');
    });

    /**
     * 以下所有API需要用户认证
     */
    Route::middleware('auth:sanctum')->group(function () {

        /**
         * Token管理
         */
        Route::prefix('auth')->group(function () {
            Route::post('/token/refresh', [\App\Soho\Http\Controllers\Api\Auth\TokenController::class, 'refresh'])->name('auth.token.refresh');
        });

        /**
         * 店铺相关API
         */
        Route::prefix('shops')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'index'])->name('shops.index');
            Route::get('/{shop}', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'show'])->name('shops.show');
            Route::post('/', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'store'])->name('shops.store');
            Route::put('/{shop}', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'update'])->name('shops.update');
            Route::delete('/{shop}', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'destroy'])->name('shops.destroy');
            Route::post('/{shop}/test-auth', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'testAuth'])->name('shops.test-auth');
            Route::post('/{shop}/refresh-token', [\App\Soho\Http\Controllers\Api\Shop\ShopController::class, 'refreshToken'])->name('shops.refresh-token');
        });

        /**
         * 订单相关API
         */
        Route::prefix('orders')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'index'])->name('orders.index');
            Route::get('/{order}', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'show'])->name('orders.show');
            Route::put('/{order}/status', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::post('/{order}/ship', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'ship'])->name('orders.ship');
            Route::get('/{order}/packages', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'packages'])->name('orders.packages');
            Route::get('/{order}/logs', [\App\Soho\Http\Controllers\Api\Order\OrderController::class, 'logs'])->name('orders.logs');
        });

        /**
         * 商品相关API
         */
        Route::prefix('products')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'index'])->name('products.index');
            Route::get('/{product}', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'show'])->name('products.show');
            Route::post('/', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'store'])->name('products.store');
            Route::put('/{product}', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'update'])->name('products.update');
            Route::delete('/{product}', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'destroy'])->name('products.destroy');
            Route::get('/{product}/skus', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'skus'])->name('products.skus');
            Route::post('/{product}/publish', [\App\Soho\Http\Controllers\Api\Product\ProductController::class, 'publish'])->name('products.publish');
        });

        /**
         * 库存相关API
         */
        Route::prefix('inventory')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\Inventory\InventoryController::class, 'index'])->name('inventory.index');
            Route::get('/{inventory}', [\App\Soho\Http\Controllers\Api\Inventory\InventoryController::class, 'show'])->name('inventory.show');
            Route::put('/{inventory}/adjust', [\App\Soho\Http\Controllers\Api\Inventory\InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::post('/transfer', [\App\Soho\Http\Controllers\Api\Inventory\InventoryController::class, 'transfer'])->name('inventory.transfer');
            Route::get('/{inventory}/transactions', [\App\Soho\Http\Controllers\Api\Inventory\InventoryController::class, 'transactions'])->name('inventory.transactions');
        });

        /**
         * 仓库相关API
         */
        Route::prefix('warehouses')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\Warehouse\WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('/{warehouse}', [\App\Soho\Http\Controllers\Api\Warehouse\WarehouseController::class, 'show'])->name('warehouses.show');
            Route::post('/', [\App\Soho\Http\Controllers\Api\Warehouse\WarehouseController::class, 'store'])->name('warehouses.store');
            Route::put('/{warehouse}', [\App\Soho\Http\Controllers\Api\Warehouse\WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('/{warehouse}', [\App\Soho\Http\Controllers\Api\Warehouse\WarehouseController::class, 'destroy'])->name('warehouses.destroy');
        });

        /**
         * 售后相关API
         */
        Route::prefix('after-sales')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Api\AfterSale\AfterSaleController::class, 'index'])->name('after-sales.index');
            Route::get('/{afterSale}', [\App\Soho\Http\Controllers\Api\AfterSale\AfterSaleController::class, 'show'])->name('after-sales.show');
            Route::put('/{afterSale}/approve', [\App\Soho\Http\Controllers\Api\AfterSale\AfterSaleController::class, 'approve'])->name('after-sales.approve');
            Route::put('/{afterSale}/reject', [\App\Soho\Http\Controllers\Api\AfterSale\AfterSaleController::class, 'reject'])->name('after-sales.reject');
            Route::put('/{afterSale}/refund', [\App\Soho\Http\Controllers\Api\AfterSale\AfterSaleController::class, 'refund'])->name('after-sales.refund');
        });

        /**
         * 物流相关API
         */
        Route::prefix('logistics')->group(function () {
            Route::get('/companies', [\App\Soho\Http\Controllers\Api\Logistics\LogisticsController::class, 'companies'])->name('logistics.companies');
            Route::get('/track/{trackingNumber}', [\App\Soho\Http\Controllers\Api\Logistics\LogisticsController::class, 'track'])->name('logistics.track');
        });

        /**
         * 报表相关API
         */
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [\App\Soho\Http\Controllers\Api\Report\ReportController::class, 'sales'])->name('reports.sales');
            Route::get('/inventory', [\App\Soho\Http\Controllers\Api\Report\ReportController::class, 'inventory'])->name('reports.inventory');
            Route::get('/profit', [\App\Soho\Http\Controllers\Api\Report\ReportController::class, 'profit'])->name('reports.profit');
        });

        /**
         * 同步任务相关API
         */
        Route::prefix('sync')->group(function () {
            Route::post('/orders', [\App\Soho\Http\Controllers\Api\Sync\SyncController::class, 'orders'])->name('sync.orders');
            Route::post('/products', [\App\Soho\Http\Controllers\Api\Sync\SyncController::class, 'products'])->name('sync.products');
            Route::post('/inventory', [\App\Soho\Http\Controllers\Api\Sync\SyncController::class, 'inventory'])->name('sync.inventory');
            Route::get('/tasks', [\App\Soho\Http\Controllers\Api\Sync\SyncController::class, 'tasks'])->name('sync.tasks');
            Route::get('/tasks/{task}', [\App\Soho\Http\Controllers\Api\Sync\SyncController::class, 'task'])->name('sync.task');
        });
    });
});
});
