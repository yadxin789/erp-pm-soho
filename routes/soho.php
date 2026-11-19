<?php

use Illuminate\Support\Facades\Route;
use App\Soho\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Soho\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| SOHO 模块路由（B-04）
|--------------------------------------------------------------------------
| 源 E:\_tmp_soho_baseline\routes\web.php（348 条），URL 加 /soho，路由名加 soho.。
*/

Route::middleware(['web','auth','module:soho','module.access:soho','log.activity'])->prefix('soho')->name('soho.')->group(function () {


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| 基于角色的路由守卫设计 - 严格权限隔离
| 每个路由的 role 中间件必须与 MenuConfig.php 中的菜单权限对齐
*/

// 根路由 - 已登录用户跳转到仪表盘，未登录用户显示欢迎页面
Route::get('/', function () {
    // 已登录用户直接进入工作台，不需要再看欢迎页面
    if (auth()->check()) {
        return redirect()->route('soho.dashboard');
    }
    // 未登录用户显示欢迎页面，点击登录按钮后再跳转到登录页
    return view('welcome');
})->name('home');

// 认证路由
Route::middleware(['auth'])->group(function () {

    // ==================== 仪表盘（所有角色可见 - 统一首页） ====================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== 统计报表（按需开放） ====================
    Route::middleware('permission:report.sales')->get('sales/report', [DashboardController::class, 'salesReport'])->name('sales.report');
    Route::middleware('permission:report.product')->get('product/ranking', [DashboardController::class, 'productRanking'])->name('product.ranking');

    // ==================== 用户管理（超级管理员 + 租户管理员） ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:user.view')->prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::get('/{user}', [\App\Soho\Http\Controllers\Admin\UserController::class, 'show'])->name('show')->whereNumber('user');
            Route::get('/{user}/edit', [\App\Soho\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [\App\Soho\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Soho\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
        });

        // 部门管理
        Route::middleware('permission:department.view')->prefix('admin/departments')->name('admin.departments.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'store'])->name('store');
            Route::get('/{department}/edit', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'edit'])->name('edit');
            Route::put('/{department}', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}', [\App\Soho\Http\Controllers\Admin\DepartmentController::class, 'destroy'])->name('destroy');
        });

        // 角色管理
        Route::middleware('permission:role.view')->prefix('admin/roles')->name('admin.roles.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'store'])->name('store');
            Route::get('/{role}/edit', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [\App\Soho\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('destroy');
        });

        // 审批流管理
        // 数据权限管理
        // 事件日志

        // 税率管理
        Route::middleware('permission:tax_rate.view')->prefix('tax-rate')->name('tax-rate.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\TaxRateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\TaxRateController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\TaxRateController::class, 'store'])->name('store');
            Route::get('/{taxRate}/edit', [\App\Soho\Http\Controllers\TaxRateController::class, 'edit'])->name('edit');
            Route::put('/{taxRate}', [\App\Soho\Http\Controllers\TaxRateController::class, 'update'])->name('update');
            Route::delete('/{taxRate}', [\App\Soho\Http\Controllers\TaxRateController::class, 'destroy'])->name('destroy');
        });

        // 货币管理
        Route::middleware('permission:currency.view')->prefix('currency')->name('currencies.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\CurrencyController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\CurrencyController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\CurrencyController::class, 'store'])->name('store');
            Route::get('/{currency}/edit', [\App\Soho\Http\Controllers\CurrencyController::class, 'edit'])->name('edit');
            Route::put('/{currency}', [\App\Soho\Http\Controllers\CurrencyController::class, 'update'])->name('update');
            Route::delete('/{currency}', [\App\Soho\Http\Controllers\CurrencyController::class, 'destroy'])->name('destroy');
            Route::patch('/{currency}/set-default', [\App\Soho\Http\Controllers\CurrencyController::class, 'setDefault'])->name('set-default');
        });

        // 导出任务
    });

    // ==================== 事件日志（V3.1：与 MenuConfig.php 对齐） ====================
    Route::middleware('soho_role:super_admin|integration_ops')->group(function () {
        Route::middleware('permission:event.view')->prefix('event-log')->name('event-log.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\EventLogController::class, 'index'])->name('index');
            Route::get('/{eventLog}', [\App\Soho\Http\Controllers\EventLogController::class, 'show'])->name('show')->whereNumber('eventLog');
            Route::post('/{eventLog}/reprocess', [\App\Soho\Http\Controllers\EventLogController::class, 'reprocess'])->name('reprocess');
        });
    });

    // ==================== 审批流管理（V3.1：与 MenuConfig.php 对齐） ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:approval_flow.view')->prefix('approval-flows')->name('approval_flows.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ApprovalFlowController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ApprovalFlowController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ApprovalFlowController::class, 'store'])->name('store');
            Route::get('/{approvalFlow}/edit', [\App\Soho\Http\Controllers\ApprovalFlowController::class, 'edit'])->name('edit');
            Route::put('/{approvalFlow}', [\App\Soho\Http\Controllers\ApprovalFlowController::class, 'update'])->name('update');
        });
    });

    // ==================== 数据权限管理（V3.1：与 MenuConfig.php 对齐） ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:data_scope.manage')->prefix('data-scopes')->name('data_scopes.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\DataScopeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\DataScopeController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\DataScopeController::class, 'store'])->name('store');
            Route::get('/{dataScope}/edit', [\App\Soho\Http\Controllers\DataScopeController::class, 'edit'])->name('edit');
            Route::put('/{dataScope}', [\App\Soho\Http\Controllers\DataScopeController::class, 'update'])->name('update');
        });
    });

    // ==================== 导出任务（V3.1：与 MenuConfig.php 对齐） ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:export_task.view')->prefix('export-tasks')->name('export_tasks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ExportTaskController::class, 'index'])->name('index');
            Route::post('/', [\App\Soho\Http\Controllers\ExportTaskController::class, 'store'])->name('store');
            Route::get('/{exportTask}/download', [\App\Soho\Http\Controllers\ExportTaskController::class, 'download'])->name('download');
        });
    });

    // ==================== 审批实例（V3.1：与 MenuConfig.php 对齐） ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:approval.instance.view')->prefix('approval-instances')->name('approval_instances.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ApprovalInstanceController::class, 'index'])->name('index');
            Route::post('/{approvalInstance}/approve', [\App\Soho\Http\Controllers\ApprovalInstanceController::class, 'approve'])->name('approve');
            Route::post('/{approvalInstance}/reject', [\App\Soho\Http\Controllers\ApprovalInstanceController::class, 'reject'])->name('reject');
        });
    });

    // ==================== 系统设置（超级管理员 + 租户管理员 + 安全管理员） ====================
    Route::middleware('soho_role:super_admin|tenant_admin|security_admin')->group(function () {
        Route::middleware('permission:system.view')->get('system/settings', [\App\Soho\Http\Controllers\System\SettingsController::class, 'index'])->name('system.settings');
        Route::middleware('permission:system.config')->put('system/settings', [\App\Soho\Http\Controllers\System\SettingsController::class, 'update'])->name('system.settings.update');
    });

    // ==================== 系统参数配置（仅超级管理员） ====================
    Route::middleware('soho_role:super_admin')->group(function () {
        // 系统参数 - 完整CRUD（管理外部平台API Key、密钥、回调地址等）
        Route::middleware('permission:system.config')->prefix('system/parameters')->name('system.parameters.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'store'])->name('store');
            Route::get('/{parameter}/edit', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'edit'])->name('edit');
            Route::put('/{parameter}', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'update'])->name('update');
            Route::delete('/{parameter}', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'destroy'])->name('destroy');
            Route::post('/{parameter}/toggle', [\App\Soho\Http\Controllers\System\SystemParameterController::class, 'toggleActive'])->name('toggle');
        });
        Route::middleware('permission:system.config')->prefix('system/features')->name('system.features.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'store'])->name('store');
            Route::get('/{feature}/edit', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'edit'])->name('edit');
            Route::put('/{feature}', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'update'])->name('update');
            Route::delete('/{feature}', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'destroy'])->name('destroy');
            Route::post('/{feature}/toggle', [\App\Soho\Http\Controllers\System\FeatureSwitchController::class, 'toggle'])->name('toggle');
        });
        // 文件存储 - 完整CRUD（管理本地/OSS/COS/七牛云等存储磁盘配置）
        Route::middleware('permission:system.config')->prefix('system/storage')->name('system.storage.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'store'])->name('store');
            Route::get('/{storage}/edit', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'edit'])->name('edit');
            Route::put('/{storage}', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'update'])->name('update');
            Route::delete('/{storage}', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'destroy'])->name('destroy');
            Route::get('/{storage}/test', [\App\Soho\Http\Controllers\System\FileStorageController::class, 'testConnection'])->name('test');
        });
    });

    // ==================== 通知模板（超级管理员 + 租户管理员） - 完整CRUD ====================
    Route::middleware('soho_role:super_admin|tenant_admin')->group(function () {
        Route::middleware('permission:system.config')->prefix('system/notifications')->name('system.notifications.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'store'])->name('store');
            Route::get('/{notification}/edit', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'edit'])->name('edit');
            Route::put('/{notification}', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'update'])->name('update');
            Route::delete('/{notification}', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{notification}/preview', [\App\Soho\Http\Controllers\System\NotificationTemplateController::class, 'preview'])->name('preview');
        });
        // 系统版本 - 只读信息页面
        Route::middleware('permission:system.view')->get('system/version', [\App\Soho\Http\Controllers\System\SystemVersionController::class, 'index'])->name('system.version');
    });

    // ==================== 安全管理（仅安全管理员） ====================
    Route::middleware('soho_role:security_admin')->group(function () {
        Route::middleware('permission:security.view')->prefix('security')->name('security.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\SecurityController::class, 'index'])->name('index');
            Route::put('/config', [\App\Soho\Http\Controllers\System\SecurityController::class, 'update'])->name('update');
            // V3.1 补全安全中心菜单
            // 登录失败记录 - 完整CRUD
            Route::get('/login-failures', [\App\Soho\Http\Controllers\Security\LoginFailureController::class, 'index'])->name('login.failures');
            Route::get('/login-failures/{failure}', [\App\Soho\Http\Controllers\Security\LoginFailureController::class, 'show'])->name('login.failures.show');
            // 会话管理 - 列表与下线
            Route::get('/sessions', [\App\Soho\Http\Controllers\Security\SessionController::class, 'index'])->name('sessions');
            Route::delete('/sessions/{sessionId}', [\App\Soho\Http\Controllers\Security\SessionController::class, 'destroy'])->name('sessions.destroy');
            Route::post('/sessions/batch-destroy', [\App\Soho\Http\Controllers\Security\SessionController::class, 'destroyBatch'])->name('sessions.destroy-batch');
            // 设备管理 - 基于会话的用户设备视图
            Route::get('/devices', [\App\Soho\Http\Controllers\Security\DeviceController::class, 'index'])->name('devices');
            Route::delete('/devices/{sessionId}', [\App\Soho\Http\Controllers\Security\DeviceController::class, 'destroy'])->name('devices.destroy');
            // IP 白名单 - 完整CRUD
            Route::middleware('permission:security.ip_whitelist')->prefix('ip-whitelist')->name('ip-whitelist.')->group(function () {
                Route::get('/', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'index'])->name('index');
                Route::get('/create', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'create'])->name('create');
                Route::post('/', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'store'])->name('store');
                Route::get('/{ipWhitelist}/edit', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'edit'])->name('edit');
                Route::put('/{ipWhitelist}', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'update'])->name('update');
                Route::delete('/{ipWhitelist}', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'destroy'])->name('destroy');
                Route::post('/{ipWhitelist}/toggle', [\App\Soho\Http\Controllers\Security\IpWhitelistController::class, 'toggle'])->name('toggle');
            });
            // 二次验证策略 - 配置页面
            Route::middleware('permission:security.config')->get('/mfa', [\App\Soho\Http\Controllers\Security\SecurityPolicyController::class, 'mfaIndex'])->name('mfa');
            Route::middleware('permission:security.config')->put('/mfa', [\App\Soho\Http\Controllers\Security\SecurityPolicyController::class, 'mfaUpdate'])->name('mfa.update');
            // 密码策略 - 配置页面
            Route::middleware('permission:security.config')->get('/password-policy', [\App\Soho\Http\Controllers\Security\SecurityPolicyController::class, 'passwordIndex'])->name('password-policy');
            Route::middleware('permission:security.config')->put('/password-policy', [\App\Soho\Http\Controllers\Security\SecurityPolicyController::class, 'passwordUpdate'])->name('password-policy.update');
            // 账号锁定记录 - 完整CRUD
            Route::get('/lockouts', [\App\Soho\Http\Controllers\Security\AccountLockoutController::class, 'index'])->name('lockouts');
            Route::get('/lockouts/{lockout}', [\App\Soho\Http\Controllers\Security\AccountLockoutController::class, 'show'])->name('lockouts.show');
            Route::post('/lockouts/{lockout}/unlock', [\App\Soho\Http\Controllers\Security\AccountLockoutController::class, 'unlock'])->name('lockouts.unlock');
        });
        Route::middleware('permission:log.audit')->get('log/audit', [\App\Soho\Http\Controllers\Log\AuditLogController::class, 'index'])->name('log.audit.index');
    });

    // ==================== 店铺中心（运营/平台运营/店铺授权/集成运维） ====================
    // V3.1：与 MenuConfig.php 店铺管理菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|platform_operator|shop_auth_admin|integration_ops|marketing_staff')->group(function () {
        Route::middleware('permission:shop.view')->prefix('shop')->name('shop.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ShopController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ShopController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ShopController::class, 'store'])->name('store');
            Route::get('/{shop}/edit', [\App\Soho\Http\Controllers\ShopController::class, 'edit'])->name('edit');
            Route::put('/{shop}', [\App\Soho\Http\Controllers\ShopController::class, 'update'])->name('update');
            Route::delete('/{shop}', [\App\Soho\Http\Controllers\ShopController::class, 'destroy'])->name('destroy');
            Route::post('/{shop}/check-auth', [\App\Soho\Http\Controllers\ShopController::class, 'checkAuth'])->name('check-auth');
            Route::post('/{shop}/refresh-token', [\App\Soho\Http\Controllers\ShopController::class, 'refreshToken'])->name('refresh-token');
        });
    });

    // ==================== 店铺授权（仅店铺授权管理员） ====================
    Route::middleware('soho_role:shop_auth_admin')->group(function () {
        Route::middleware('permission:shop.authorize')->get('shop/authorize', [\App\Soho\Http\Controllers\ShopController::class, 'authorize'])->name('shop.authorize');
        // V3.1 补全授权运维菜单 - 已实现
        // 授权状态监控
        Route::middleware('permission:shop.authorize')->prefix('shop/authorization-status')->name('shop.authorization-status.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Shop\AuthorizationStatusController::class, 'index'])->name('index');
            Route::get('/shop/{shop}', [\App\Soho\Http\Controllers\Shop\AuthorizationStatusController::class, 'showShop'])->name('show-shop');
            Route::get('/platform-account/{platformAccount}', [\App\Soho\Http\Controllers\Shop\AuthorizationStatusController::class, 'showPlatformAccount'])->name('show-platform-account');
        });
        // Token状态监控（已实现）
        Route::middleware('permission:shop.authorize')->get('shop/token-status', [\App\Soho\Http\Controllers\Shop\TokenStatusController::class, 'index'])->name('shop.token-status');
        // 授权操作日志（已实现）
        Route::middleware('permission:shop.authorize')->prefix('shop/authorization-logs')->name('shop.authorization-logs.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Shop\AuthorizationLogController::class, 'index'])->name('index');
            Route::get('/{log}', [\App\Soho\Http\Controllers\Shop\AuthorizationLogController::class, 'show'])->name('show')->whereNumber('log');
        });
        // 授权失败记录
        Route::middleware('permission:shop.authorize')->prefix('shop/authorization-failures')->name('shop.authorization-failures.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Shop\AuthorizationFailureController::class, 'index'])->name('index');
            Route::get('/{failure}', [\App\Soho\Http\Controllers\Shop\AuthorizationFailureController::class, 'show'])->name('show')->whereNumber('failure');
            Route::post('/{failure}/resolve', [\App\Soho\Http\Controllers\Shop\AuthorizationFailureController::class, 'resolve'])->name('resolve');
        });
        // Token刷新记录
        Route::middleware('permission:shop.authorize')->prefix('shop/token-refresh-logs')->name('shop.token-refresh-logs.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Shop\TokenRefreshLogController::class, 'index'])->name('index');
            Route::get('{log}', [\App\Soho\Http\Controllers\Shop\TokenRefreshLogController::class, 'show'])->name('show');
        });
        // 授权异常（已实现）
        Route::middleware('permission:shop.authorize')->prefix('shop/authorization-exceptions')->name('shop.authorization-exceptions.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Shop\AuthorizationExceptionController::class, 'index'])->name('index');
            Route::get('/{authorizationException}', [\App\Soho\Http\Controllers\Shop\AuthorizationExceptionController::class, 'show'])->name('show')->whereNumber('authorizationException');
            Route::post('/{authorizationException}/handle', [\App\Soho\Http\Controllers\Shop\AuthorizationExceptionController::class, 'handle'])->name('handle');
        });
    });

    // ==================== 平台账号与能力（店铺授权管理员 + 集成运维） ====================
    Route::middleware('soho_role:shop_auth_admin|integration_ops')->group(function () {
        // 平台账号管理
        Route::middleware('permission:platform_account.view')->prefix('platform-accounts')->name('platform_accounts.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'store'])->name('store');
            Route::get('/{platformAccount}/edit', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'edit'])->name('edit');
            Route::put('/{platformAccount}', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'update'])->name('update');
            Route::post('/{platformAccount}/authorize', [\App\Soho\Http\Controllers\PlatformAccountController::class, 'authorize'])->name('authorize');
        });

        // 平台能力
        Route::middleware('permission:platform_account.view')->get('platform-capabilities', [\App\Soho\Http\Controllers\PlatformCapabilityController::class, 'index'])->name('platform_capabilities.index');
    });

    // ==================== 商品中心（商品管理员/运营/平台运营/定价/数据分析等） ====================
    // V3.1 严格前后台分离：与 MenuConfig.php 商品中心菜单对齐
    Route::middleware('soho_role:super_admin|operation_manager|product_admin|product_reviewer|platform_operator|pricing_staff|read_only')->group(function () {

        // 商品 SPU 管理
        Route::middleware('permission:product.view')->prefix('product')->name('product.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ProductController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ProductController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [\App\Soho\Http\Controllers\ProductController::class, 'show'])->name('show')->whereNumber('product');
            Route::get('/{product}/edit', [\App\Soho\Http\Controllers\ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [\App\Soho\Http\Controllers\ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [\App\Soho\Http\Controllers\ProductController::class, 'destroy'])->name('destroy');
        });

        // 商品管理
        Route::middleware('permission:product.view')->prefix('goods')->name('goods.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\GoodsController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\GoodsController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\GoodsController::class, 'store'])->name('store');
            Route::get('/{goods}/edit', [\App\Soho\Http\Controllers\GoodsController::class, 'edit'])->name('edit');
            Route::put('/{goods}', [\App\Soho\Http\Controllers\GoodsController::class, 'update'])->name('update');
            Route::delete('/{goods}', [\App\Soho\Http\Controllers\GoodsController::class, 'destroy'])->name('destroy');
            Route::post('/batch-publish', [\App\Soho\Http\Controllers\GoodsController::class, 'batchPublish'])->name('batch-publish');
        });
    });

    // ==================== 商品报表 ====================
    // V3.1：与 MenuConfig.php 商品报表菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|product_admin|product_reviewer|platform_operator|pricing_staff|data_analyst|auditor|read_only|marketing_staff')->group(function () {
        Route::middleware('permission:report.product')->get('report/product', [\App\Soho\Http\Controllers\Report\ProductReportController::class, 'index'])->name('report.product');
        Route::middleware('permission:report.product')->get('report/product/export', [\App\Soho\Http\Controllers\Report\ProductReportController::class, 'export'])->name('report.product.export');
    });

    // ==================== SKU 映射（多角色可查看） ====================
    // V3.1：与 MenuConfig.php SKU 映射菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|product_admin|product_reviewer|platform_operator|pricing_staff|read_only')->group(function () {
        Route::middleware('permission:sku.view')->prefix('platform-sku')->name('platform-sku.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'store'])->name('store');
            Route::get('/{platformSku}/edit', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'edit'])->name('edit');
            Route::put('/{platformSku}', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'update'])->name('update');
            Route::delete('/{platformSku}', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'destroy'])->name('destroy');
            Route::post('/import', [\App\Soho\Http\Controllers\PlatformSkuController::class, 'import'])->name('import');
        });
    });

    // ==================== 品牌/分类管理（商品管理员/运营） ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:super_admin|operation_manager|product_admin|product_reviewer|platform_operator')->group(function () {
        Route::middleware('permission:brand.view')->prefix('brand')->name('brand.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\BrandController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\BrandController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\BrandController::class, 'store'])->name('store');
            Route::get('/{brand}/edit', [\App\Soho\Http\Controllers\BrandController::class, 'edit'])->name('edit');
            Route::put('/{brand}', [\App\Soho\Http\Controllers\BrandController::class, 'update'])->name('update');
            Route::delete('/{brand}', [\App\Soho\Http\Controllers\BrandController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:category.view')->prefix('category')->name('category.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\CategoryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\CategoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [\App\Soho\Http\Controllers\CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [\App\Soho\Http\Controllers\CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [\App\Soho\Http\Controllers\CategoryController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 商品属性管理 ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:super_admin|product_admin|operation_manager|platform_operator')->group(function () {
        Route::middleware('permission:product.attribute.view')->prefix('product-attribute')->name('product-attribute.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'store'])->name('store');
            Route::get('/{productAttribute}/edit', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'edit'])->name('edit');
            Route::put('/{productAttribute}', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'update'])->name('update');
            Route::delete('/{productAttribute}', [\App\Soho\Http\Controllers\ProductAttributeController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 媒体库 ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:super_admin|product_admin|platform_operator|operation_manager|product_reviewer')->group(function () {
        Route::middleware('permission:media.view')->prefix('media')->name('media.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\MediumController::class, 'index'])->name('index');
            Route::post('/upload', [\App\Soho\Http\Controllers\MediumController::class, 'upload'])->name('upload');
            Route::get('/{medium}/download', [\App\Soho\Http\Controllers\MediumController::class, 'download'])->name('download');
            Route::delete('/{medium}', [\App\Soho\Http\Controllers\MediumController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 订单中心 ====================
    // V3.1：与 MenuConfig.php 订单中心菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|order_manager|customer_service|read_only')->group(function () {
        Route::middleware('permission:order.view')->prefix('order')->name('order.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\OrderController::class, 'index'])->name('index');
            Route::post('/batch-ship', [\App\Soho\Http\Controllers\OrderController::class, 'batchShip'])->name('batch-ship');
            Route::get('/export', [\App\Soho\Http\Controllers\OrderController::class, 'export'])->name('export');
            Route::get('/pending-payment', [\App\Soho\Http\Controllers\OrderController::class, 'pendingPayment'])->name('pending-payment');
            Route::get('/pending-ship', [\App\Soho\Http\Controllers\OrderController::class, 'pendingShip'])->name('pending-ship');
            Route::get('/shipped', [\App\Soho\Http\Controllers\OrderController::class, 'shipped'])->name('shipped');
            Route::get('/completed', [\App\Soho\Http\Controllers\OrderController::class, 'completed'])->name('completed');
            Route::get('/cancelled', [\App\Soho\Http\Controllers\OrderController::class, 'cancelled'])->name('cancelled');
            Route::get('/exception', [\App\Soho\Http\Controllers\OrderController::class, 'exception'])->name('exception');
            Route::get('/{order}', [\App\Soho\Http\Controllers\OrderController::class, 'show'])->name('show')->whereNumber('order');
            Route::post('/{order}/ship', [\App\Soho\Http\Controllers\OrderController::class, 'ship'])->name('ship');
            Route::post('/{order}/freeze', [\App\Soho\Http\Controllers\OrderController::class, 'freeze'])->name('freeze');
            Route::put('/{order}/remark', [\App\Soho\Http\Controllers\OrderController::class, 'remark'])->name('remark');
            Route::post('/{order}/cancel', [\App\Soho\Http\Controllers\OrderController::class, 'cancel'])->name('cancel');
        });
    });

    // ==================== 客户中心 ====================
    // V3.1：与 MenuConfig.php 客户中心菜单角色对齐
    Route::middleware('soho_role:super_admin|customer_service|operation_manager')->group(function () {
        Route::middleware('permission:customer.view')->prefix('customer')->name('customer.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\CustomerController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\CustomerController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\CustomerController::class, 'store'])->name('store');
            Route::get('/groups', [\App\Soho\Http\Controllers\CustomerController::class, 'groups'])->name('groups');
            Route::post('/groups', [\App\Soho\Http\Controllers\CustomerController::class, 'groupStore'])->name('groups.store');
            Route::put('/groups/{group}', [\App\Soho\Http\Controllers\CustomerController::class, 'groupUpdate'])->name('groups.update');
            Route::delete('/groups/{group}', [\App\Soho\Http\Controllers\CustomerController::class, 'groupDestroy'])->name('groups.destroy');
            Route::get('/{customer}', [\App\Soho\Http\Controllers\CustomerController::class, 'show'])->name('show')->whereNumber('customer');
            Route::get('/{customer}/edit', [\App\Soho\Http\Controllers\CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [\App\Soho\Http\Controllers\CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [\App\Soho\Http\Controllers\CustomerController::class, 'destroy'])->name('destroy');
            Route::post('/{customer}/assign-group', [\App\Soho\Http\Controllers\CustomerController::class, 'assignGroup'])->name('assign-group');
        });
    });

    // ==================== 促销管理 ====================
    // V3.1：与 MenuConfig.php 营销中心菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|platform_operator|marketing_staff')->group(function () {
        Route::middleware('permission:promotion.view')->prefix('promotion')->name('promotion.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\PromotionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\PromotionController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\PromotionController::class, 'store'])->name('store');
            Route::get('/{promotion}/edit', [\App\Soho\Http\Controllers\PromotionController::class, 'edit'])->name('edit');
            Route::put('/{promotion}', [\App\Soho\Http\Controllers\PromotionController::class, 'update'])->name('update');
            Route::delete('/{promotion}', [\App\Soho\Http\Controllers\PromotionController::class, 'destroy'])->name('destroy');
            Route::patch('/{promotion}/toggle', [\App\Soho\Http\Controllers\PromotionController::class, 'toggleStatus'])->name('toggle');
        });

        Route::middleware('permission:coupon.view')->prefix('coupon')->name('coupon.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\CouponController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\CouponController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\CouponController::class, 'store'])->name('store');
            Route::post('/generate', [\App\Soho\Http\Controllers\CouponController::class, 'generate'])->name('generate');
            Route::get('/{coupon}/edit', [\App\Soho\Http\Controllers\CouponController::class, 'edit'])->name('edit');
            Route::put('/{coupon}', [\App\Soho\Http\Controllers\CouponController::class, 'update'])->name('update');
            Route::delete('/{coupon}', [\App\Soho\Http\Controllers\CouponController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 物流包裹（多角色） ====================
    // V3.1：与 MenuConfig.php 包裹管理菜单角色对齐
    Route::middleware('soho_role:order_manager|warehouse_manager|packer|logistics_specialist')->group(function () {
        Route::middleware('permission:package.view')->prefix('package')->name('package.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\PackageController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\PackageController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\PackageController::class, 'store'])->name('store');
            Route::get('/{package}/edit', [\App\Soho\Http\Controllers\PackageController::class, 'edit'])->name('edit');
            Route::put('/{package}', [\App\Soho\Http\Controllers\PackageController::class, 'update'])->name('update');
        });
    });

    // ==================== 快递公司（多角色查看） ====================
    // V3.1：与 MenuConfig.php 快递公司菜单角色对齐
    Route::middleware('soho_role:order_manager|warehouse_manager|packer|logistics_specialist')->group(function () {
        Route::middleware('permission:express.view')->prefix('express')->name('express.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ExpressController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ExpressController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ExpressController::class, 'store'])->name('store');
            Route::get('/{express}/edit', [\App\Soho\Http\Controllers\ExpressController::class, 'edit'])->name('edit');
            Route::put('/{express}', [\App\Soho\Http\Controllers\ExpressController::class, 'update'])->name('update');
            Route::delete('/{express}', [\App\Soho\Http\Controllers\ExpressController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 物流配送 ====================
    // V3.1：与 MenuConfig.php 物流配送菜单角色对齐
    Route::middleware('soho_role:warehouse_manager|packer|logistics_specialist')->group(function () {
        Route::middleware('permission:shipping.view')->prefix('shipping-method')->name('shipping-method.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'store'])->name('store');
            Route::get('/{shippingMethod}/edit', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'edit'])->name('edit');
            Route::put('/{shippingMethod}', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{shippingMethod}', [\App\Soho\Http\Controllers\ShippingMethodController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:shipping.view')->prefix('shipping-template')->name('shipping-templates.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'store'])->name('store');
            Route::get('/{shippingTemplate}/edit', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'edit'])->name('edit');
            Route::put('/{shippingTemplate}', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'update'])->name('update');
            Route::delete('/{shippingTemplate}', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/calculate-fee', [\App\Soho\Http\Controllers\ShippingTemplateController::class, 'calculateFee'])->name('calculate-fee');
        });
    });

    // ==================== 仓储中心（多角色） ====================
    // V3.1：与 MenuConfig.php 仓储中心菜单角色对齐
    Route::middleware('soho_role:super_admin|warehouse_manager|operation_manager|order_manager|data_analyst|read_only')->group(function () {
        // 仓库管理
        Route::middleware('permission:warehouse.view')->prefix('warehouse')->name('warehouse.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\WarehouseController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\WarehouseController::class, 'store'])->name('store');
            Route::get('/{warehouse}/edit', [\App\Soho\Http\Controllers\WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{warehouse}', [\App\Soho\Http\Controllers\WarehouseController::class, 'update'])->name('update');
            Route::delete('/{warehouse}', [\App\Soho\Http\Controllers\WarehouseController::class, 'destroy'])->name('destroy');
        });

        // 库存查询
        Route::middleware('permission:inventory.view')->prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\InventoryController::class, 'index'])->name('index');
            Route::get('/export', [\App\Soho\Http\Controllers\InventoryController::class, 'export'])->name('export');
            Route::get('/flow', [\App\Soho\Http\Controllers\InventoryController::class, 'flow'])->name('flow');
        });
    });

    // ==================== 库存操作（仅仓库管理员） ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:warehouse_manager')->group(function () {
        Route::middleware('permission:inventory.view')->prefix('inventory')->name('inventory.')->group(function () {
            // 入库管理
            Route::middleware('permission:inventory.adjust')->get('/in', [\App\Soho\Http\Controllers\InventoryController::class, 'inbound'])->name('in');
            Route::middleware('permission:inventory.adjust')->post('/in', [\App\Soho\Http\Controllers\InventoryController::class, 'storeInbound'])->name('in.store');
            // 出库管理
            Route::middleware('permission:inventory.adjust')->get('/out', [\App\Soho\Http\Controllers\InventoryController::class, 'outbound'])->name('out');
            Route::middleware('permission:inventory.adjust')->post('/out', [\App\Soho\Http\Controllers\InventoryController::class, 'storeOutbound'])->name('out.store');
            // 库存调拨
            Route::middleware('permission:inventory.transfer')->get('/transfer', [\App\Soho\Http\Controllers\InventoryController::class, 'transfer'])->name('transfer');
            Route::middleware('permission:inventory.transfer')->post('/transfer', [\App\Soho\Http\Controllers\InventoryController::class, 'storeTransfer'])->name('transfer.store');
            // 库存盘点
            Route::middleware('permission:inventory.stocktake')->get('/stocktake', [\App\Soho\Http\Controllers\InventoryController::class, 'stocktake'])->name('stocktake');
            Route::middleware('permission:inventory.stocktake')->post('/stocktake', [\App\Soho\Http\Controllers\InventoryController::class, 'storeStocktake'])->name('stocktake.store');
            // 库存流水
            Route::get('/flow', [\App\Soho\Http\Controllers\InventoryController::class, 'flow'])->name('flow');
        });

        // 库存单据
        Route::middleware('permission:inventory.document.view')->get('inventory/documents', [\App\Soho\Http\Controllers\InventoryDocumentController::class, 'index'])->name('inventory.documents');
        Route::middleware('permission:inventory.document.create')->post('inventory/documents', [\App\Soho\Http\Controllers\InventoryDocumentController::class, 'store'])->name('inventory.documents.store');

        // 库存批次
        Route::middleware('permission:inventory.batch.view')->get('inventory/batches', [\App\Soho\Http\Controllers\InventoryBatchController::class, 'index'])->name('inventory.batches');

        // 库存同步日志
        Route::middleware('permission:log.view')->get('log/stock-sync', [\App\Soho\Http\Controllers\Log\StockSyncLogController::class, 'index'])->name('log.stock-sync.index');
    });

    // ==================== 库存批次（采购员也可查看） ====================
    Route::middleware('soho_role:procurement_staff')->group(function () {
        Route::middleware('permission:inventory.batch.view')->get('inventory/batches', [\App\Soho\Http\Controllers\InventoryBatchController::class, 'index'])->name('inventory.batches');
    });

    // ==================== 售后中心 ====================
    // V3.1：与 MenuConfig.php 售后中心菜单角色对齐
    Route::middleware('soho_role:super_admin|customer_service|aftersale_manager')->group(function () {
        Route::middleware('permission:after_sale.view')->prefix('aftersale')->name('aftersale.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\AftersaleController::class, 'index'])->name('index');
            Route::middleware('permission:after_sale.approve')->get('/pending', [\App\Soho\Http\Controllers\AftersaleController::class, 'pending'])->name('pending');
            Route::middleware('permission:after_sale.refund')->get('/refunds', [\App\Soho\Http\Controllers\AftersaleController::class, 'refunds'])->name('refunds');
            Route::get('/{aftersale}', [\App\Soho\Http\Controllers\AftersaleController::class, 'show'])->name('show')->whereNumber('aftersale');
            Route::put('/{aftersale}/handle', [\App\Soho\Http\Controllers\AftersaleController::class, 'handle'])->name('handle');
        });
    });

    // ==================== 财务中心 ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:finance_staff|finance_manager')->group(function () {
        // 收款记录
        Route::middleware('permission:finance.view')->get('finance/receive', [\App\Soho\Http\Controllers\FinanceController::class, 'receive'])->name('finance.receive');
        // 对账管理
        Route::middleware('permission:finance.reconcile')->get('finance/reconcile', [\App\Soho\Http\Controllers\FinanceController::class, 'reconcile'])->name('finance.reconcile');
        Route::middleware('permission:finance.reconcile')->post('finance/reconcile', [\App\Soho\Http\Controllers\FinanceController::class, 'storeReconcile'])->name('finance.reconcile.store');
        // 结算管理
        Route::middleware('permission:finance.settle')->get('finance/settle', [\App\Soho\Http\Controllers\FinanceController::class, 'settle'])->name('finance.settle');
        Route::middleware('permission:finance.settle')->post('finance/settle', [\App\Soho\Http\Controllers\FinanceController::class, 'storeSettle'])->name('finance.settle.store');
    });

    // ==================== 退款记录 ====================
    // V3.1：与 MenuConfig.php 退款记录菜单角色对齐
    Route::middleware('soho_role:customer_service|aftersale_manager')->group(function () {
        Route::middleware('permission:after_sale.refund')->get('finance/refunds', [\App\Soho\Http\Controllers\FinanceController::class, 'refunds'])->name('finance.refunds');
    });

    // ==================== 财务流水 ====================
    // V3.1：与 MenuConfig.php 财务流水菜单角色对齐
    Route::middleware('soho_role:finance_staff|finance_manager')->group(function () {
        Route::middleware('permission:finance.view')->get('finance/transactions', [\App\Soho\Http\Controllers\FinanceTransactionController::class, 'index'])->name('finance.transactions');
    });

    // ==================== 利润报表 / 财务报表 ====================
    // V3.1：与 MenuConfig.php 利润报表 + 财务报表菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|finance_manager|pricing_staff|data_analyst|auditor')->group(function () {
        Route::middleware('permission:report.profit')->get('report/profit', [\App\Soho\Http\Controllers\Report\ProfitReportController::class, 'index'])->name('report.profit');
        Route::middleware('permission:report.profit')->get('report/profit/export', [\App\Soho\Http\Controllers\Report\ProfitReportController::class, 'export'])->name('report.profit.export');
    });

    // ==================== 财务主管额外功能 ====================
    Route::middleware('soho_role:finance_manager')->group(function () {
        // 税务主体
        Route::middleware('permission:tax_entity.view')->prefix('tax-entities')->name('tax_entities.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\TaxEntityController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\TaxEntityController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\TaxEntityController::class, 'store'])->name('store');
            Route::get('/{taxEntity}/edit', [\App\Soho\Http\Controllers\TaxEntityController::class, 'edit'])->name('edit');
            Route::put('/{taxEntity}', [\App\Soho\Http\Controllers\TaxEntityController::class, 'update'])->name('update');
        });

        // 汇率管理
        Route::middleware('permission:exchange_rate.view')->prefix('exchange-rates')->name('exchange_rates.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ExchangeRateController::class, 'index'])->name('index');
            Route::post('/', [\App\Soho\Http\Controllers\ExchangeRateController::class, 'store'])->name('store');
        });
    });

    // ==================== 财务人员汇率查看 ====================
    Route::middleware('soho_role:finance_staff')->group(function () {
        Route::middleware('permission:exchange_rate.view')->get('exchange-rates', [\App\Soho\Http\Controllers\ExchangeRateController::class, 'index'])->name('exchange_rates.index');
    });

    // ==================== 同步任务中心 ====================
    // V3.1：operation_manager 已移除 - 运营主管不负责系统运维
    Route::middleware('soho_role:shop_auth_admin|integration_ops')->group(function () {
        Route::middleware('permission:sync_task.view')->prefix('sync-task')->name('sync-task.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\SyncTaskController::class, 'index'])->name('index');
            Route::post('/batch-execute', [\App\Soho\Http\Controllers\SyncTaskController::class, 'batchExecute'])->name('batch-execute');
            Route::get('/{syncTask}', [\App\Soho\Http\Controllers\SyncTaskController::class, 'show'])->name('show')->whereNumber('syncTask');
            Route::post('/{syncTask}/execute', [\App\Soho\Http\Controllers\SyncTaskController::class, 'execute'])->name('execute');
        });

        // 店铺连接状态监控
        Route::middleware('permission:sync_task.view')->get('monitor/shop-connection', [\App\Soho\Http\Controllers\Shop\ShopConnectionController::class, 'index'])->name('monitor.shop-connection');
    });

    // ==================== 同步日志 ====================
    // V3.1：与 MenuConfig.php 同步日志菜单角色对齐
    Route::middleware('soho_role:integration_ops|shop_auth_admin')->group(function () {
        Route::middleware('permission:log.view')->get('log/sync-task', [\App\Soho\Http\Controllers\Log\SyncTaskLogController::class, 'index'])->name('log.sync-task.index');
    });

    // ==================== 同步游标（仅集成运维） ====================
    // V3.1：super_admin 已移除
    Route::middleware('soho_role:integration_ops')->group(function () {
        Route::middleware('permission:sync_cursor.view')->prefix('sync-cursors')->name('sync_cursors.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\SyncCursorController::class, 'index'])->name('index');
            Route::post('/{syncCursor}/reset', [\App\Soho\Http\Controllers\SyncCursorController::class, 'reset'])->name('reset');
        });
    });

    // ==================== 系统运维日志 ====================
    // V3.1：与 MenuConfig.php API日志菜单角色对齐
    Route::middleware('soho_role:super_admin|security_admin|integration_ops')->group(function () {
        Route::middleware('permission:log.api')->get('log/platform-api', [\App\Soho\Http\Controllers\Log\PlatformApiLogController::class, 'index'])->name('log.platform-api.index');
    });

    // ==================== 操作日志 ====================
    // V3.1：与 MenuConfig.php 操作日志菜单角色对齐
    Route::middleware('soho_role:tenant_admin|security_admin|auditor')->group(function () {
        Route::middleware('permission:log.view')->get('operation-logs', [\App\Soho\Http\Controllers\OperationLogController::class, 'index'])->name('operation-logs.index');
    });

    // ==================== 异常中心 ====================
    // V3.1：与 MenuConfig.php 异常中心菜单角色对齐
    Route::middleware('soho_role:integration_ops|super_admin')->group(function () {
        Route::middleware('permission:exception.view')->prefix('exceptions')->name('exceptions.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'index'])->name('index');
            Route::get('/{exceptionCase}', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'show'])->name('show')->whereNumber('exceptionCase');
            Route::post('/{exceptionCase}/handle', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'handle'])->name('handle');
            Route::post('/{exceptionCase}/assign', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'assign'])->name('assign');
            Route::post('/{exceptionCase}/resolve', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'resolve'])->name('resolve');
            Route::post('/{exceptionCase}/ignore', [\App\Soho\Http\Controllers\ExceptionCaseController::class, 'ignore'])->name('ignore');
        });
    });

    // ==================== 销售报表 ====================
    // V3.1：与 MenuConfig.php 销售报表菜单角色对齐
    Route::middleware('soho_role:super_admin|operation_manager|finance_manager|pricing_staff|data_analyst|auditor|read_only|marketing_staff')->group(function () {
        Route::middleware('permission:report.sales')->get('report/sales', [\App\Soho\Http\Controllers\Report\SalesReportController::class, 'index'])->name('report.sales');
        Route::middleware('permission:report.sales')->get('report/sales/export', [\App\Soho\Http\Controllers\Report\SalesReportController::class, 'export'])->name('report.sales.export');
    });

    // ==================== 库存报表 ====================
    // V3.1：与 MenuConfig.php 库存报表菜单角色对齐
    Route::middleware('soho_role:super_admin|data_analyst|auditor|read_only')->group(function () {
        Route::middleware('permission:report.stock')->get('report/inventory', [\App\Soho\Http\Controllers\Report\InventoryReportController::class, 'index'])->name('report.inventory');
        Route::middleware('permission:report.stock')->get('report/inventory/export', [\App\Soho\Http\Controllers\Report\InventoryReportController::class, 'export'])->name('report.inventory.export');
    });

    // ==================== 订单时效报表 ====================
    // 修复 P0：补充 role + permission 双重守卫，防止未授权访问
    // 角色范围与 report/sales 对齐，并补充 order_manager（订单主管需查看订单时效）
    Route::middleware('soho_role:super_admin|operation_manager|order_manager|data_analyst|auditor|read_only')->group(function () {
        Route::middleware('permission:report.sales')->get('report/order-timeliness', [\App\Soho\Http\Controllers\Report\OrderTimelinessController::class, 'index'])->name('report.order-timeliness');
        Route::middleware('permission:report.sales')->get('report/order-timeliness/export', [\App\Soho\Http\Controllers\Report\OrderTimelinessController::class, 'export'])->name('report.order-timeliness.export');
    });

    // ==================== 库存概览 ====================
    // 修复 P0：补充 role + permission 双重守卫，防止未授权访问
    // 角色范围与 inventory 域路由对齐
    Route::middleware('soho_role:super_admin|warehouse_manager|operation_manager|order_manager|data_analyst|read_only')->group(function () {
        Route::middleware('permission:inventory.view')->get('inventory/overview', [DashboardController::class, 'inventoryOverview'])->name('inventory.overview');
    });

    // ==================== 通知管理（所有已登录用户） ====================
    Route::group([], function () {
        Route::get('/notifications', [\App\Soho\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [\App\Soho\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/read-all', [\App\Soho\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::get('/notifications/delete-read', [\App\Soho\Http\Controllers\NotificationController::class, 'deleteRead'])->name('notifications.delete-read');
        Route::post('/notifications/batch-action', [\App\Soho\Http\Controllers\NotificationController::class, 'batchAction'])->name('notifications.batch-action');
        Route::patch('/notifications/{notification}/read', [\App\Soho\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/{notification}', [\App\Soho\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // ==================== 个人资料（头像更新） ====================
    Route::post('/profile/avatar', [\App\Soho\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('/profile/avatar/defaults', [\App\Soho\Http\Controllers\ProfileController::class, 'defaultAvatars'])->name('profile.avatar.defaults');

    // ==================== 聊天室功能 ====================
    Route::group([], function () {
        Route::get('/chat', [\App\Soho\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/messages', [\App\Soho\Http\Controllers\ChatController::class, 'index'])->name('chat.messages');
        Route::post('/chat/send', [\App\Soho\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
        Route::get('/chat/conversations', [\App\Soho\Http\Controllers\ChatController::class, 'conversations'])->name('chat.conversations');
        Route::get('/chat/private/{userId}', [\App\Soho\Http\Controllers\ChatController::class, 'privateMessages'])->name('chat.private');
        Route::delete('/chat/messages/{message}', [\App\Soho\Http\Controllers\ChatController::class, 'destroy'])->name('chat.destroy');
        // 清空当前用户留言板（群聊/私聊）- 软清空，只影响当前用户显示
        Route::post('/chat/clear-group', [\App\Soho\Http\Controllers\ChatController::class, 'clearGroup'])->name('chat.clear-group');
        Route::post('/chat/clear-private/{userId}', [\App\Soho\Http\Controllers\ChatController::class, 'clearPrivate'])->name('chat.clear-private');
    });

    // ==================== V3.1 新增菜单组占位路由 ====================

    // ==================== 价格中心（定价人员/运营主管/供应链主管/平台运营） ====================
    Route::middleware('soho_role:pricing_staff|operation_manager|procurement_manager|platform_operator')->group(function () {
        // 价格查询
        Route::middleware('permission:price.view')->get('price/query', [\App\Soho\Http\Controllers\Price\PriceQueryController::class, 'index'])->name('price.query');

        // 价格规则
        Route::middleware('permission:price.rule.view')->prefix('price/rules')->name('price.rules.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'store'])->name('store');
            Route::get('/{priceRule}/edit', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'edit'])->name('edit');
            Route::put('/{priceRule}', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'update'])->name('update');
            Route::delete('/{priceRule}', [\App\Soho\Http\Controllers\Price\PriceRuleController::class, 'destroy'])->name('destroy');
        });

        // 价格计算
        Route::middleware('permission:price.view')->get('price/calculate', [\App\Soho\Http\Controllers\Price\PriceCalculateController::class, 'index'])->name('price.calculate');

        // 调价申请
        Route::middleware('permission:price.change.request')->prefix('price/change/requests')->name('price.change.requests.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'store'])->name('store');
            Route::get('/{priceChangeRequest}', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'show'])->name('show')->whereNumber('priceChangeRequest');
            Route::get('/{priceChangeRequest}/edit', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'edit'])->name('edit');
            Route::put('/{priceChangeRequest}', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'update'])->name('update');
            Route::delete('/{priceChangeRequest}', [\App\Soho\Http\Controllers\Price\PriceChangeRequestController::class, 'destroy'])->name('destroy');
        });

        // 调价审批
        Route::middleware('permission:price.change.approve')->prefix('price/change/approvals')->name('price.change.approvals.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Price\PriceChangeApprovalController::class, 'index'])->name('index');
            Route::get('/{priceChangeRequest}', [\App\Soho\Http\Controllers\Price\PriceChangeApprovalController::class, 'show'])->name('show')->whereNumber('priceChangeRequest');
            Route::post('/{priceChangeRequest}/approve', [\App\Soho\Http\Controllers\Price\PriceChangeApprovalController::class, 'approve'])->name('approve');
            Route::post('/{priceChangeRequest}/reject', [\App\Soho\Http\Controllers\Price\PriceChangeApprovalController::class, 'reject'])->name('reject');
        });

        // 调价任务
        Route::middleware('permission:price.change.execute')->prefix('price/change/tasks')->name('price.change.tasks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'index'])->name('index');
            Route::get('/create', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'create'])->name('create');
            Route::post('/', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'store'])->name('store');
            Route::get('/{priceChangeTask}', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'show'])->name('show')->whereNumber('priceChangeTask');
            Route::post('/{priceChangeTask}/execute', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'execute'])->name('execute');
            Route::delete('/{priceChangeTask}', [\App\Soho\Http\Controllers\Price\PriceChangeTaskController::class, 'destroy'])->name('destroy');
        });

        // 调价历史
        Route::middleware('permission:price.view')->get('price/change/history', function () {
            return view('placeholder', ['title' => '调价历史', 'feature' => '调价历史', 'status' => '开发中']);
        })->name('price.change.history');
    });

    // ==================== 履约作业（仓库岗位专用） ====================
    Route::middleware('soho_role:picker')->group(function () {
        // 拣货任务
        Route::middleware('permission:pick.task.view')->prefix('pick/tasks')->name('pick.tasks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Fulfillment\PickTaskController::class, 'index'])->name('index');
            Route::get('/scan', [\App\Soho\Http\Controllers\Fulfillment\PickTaskController::class, 'scan'])->name('scan');
            Route::get('/exception', [\App\Soho\Http\Controllers\Fulfillment\PickTaskController::class, 'exception'])->name('exception');
            Route::get('/progress', [\App\Soho\Http\Controllers\Fulfillment\PickTaskController::class, 'progress'])->name('progress');
            Route::get('/completed', [\App\Soho\Http\Controllers\Fulfillment\PickTaskController::class, 'completed'])->name('completed');
        });
    });

    Route::middleware('soho_role:checker')->group(function () {
        // 复核任务
        Route::middleware('permission:check.task.view')->prefix('check/tasks')->name('check.tasks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Fulfillment\CheckTaskController::class, 'index'])->name('index');
            Route::get('/scan', [\App\Soho\Http\Controllers\Fulfillment\CheckTaskController::class, 'scan'])->name('scan');
            Route::get('/exception', [\App\Soho\Http\Controllers\Fulfillment\CheckTaskController::class, 'exception'])->name('exception');
            Route::get('/completed', [\App\Soho\Http\Controllers\Fulfillment\CheckTaskController::class, 'completed'])->name('completed');
            Route::get('/record', [\App\Soho\Http\Controllers\Fulfillment\CheckTaskController::class, 'record'])->name('record');
        });
    });

    Route::middleware('soho_role:packer')->group(function () {
        // 打包任务
        Route::middleware('permission:pack.task.view')->prefix('pack/tasks')->name('pack.tasks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'index'])->name('index');
            Route::get('/scan', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'scan'])->name('scan');
            Route::get('/material', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'material'])->name('material');
            Route::get('/weight', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'weight'])->name('weight');
            Route::get('/parcel', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'parcel'])->name('parcel');
            Route::get('/exception', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'exception'])->name('exception');
            Route::get('/completed', [\App\Soho\Http\Controllers\Fulfillment\PackTaskController::class, 'completed'])->name('completed');
        });
    });

    // 作业协同 - 订单主管与仓库主管可查看
    Route::middleware('soho_role:order_manager|warehouse_manager')->group(function () {
        // 待发货包裹
        Route::middleware('permission:shipment.view')->get('shipment/pending', [\App\Soho\Http\Controllers\Fulfillment\ShipmentController::class, 'pending'])->name('shipment.pending');

        // 作业异常
        Route::middleware('permission:fulfillment.view')->get('fulfillment/exception', [\App\Soho\Http\Controllers\Fulfillment\FulfillmentExceptionController::class, 'index'])->name('fulfillment.exception');
    });

    // ==================== 采购中心（采购员/采购主管） ====================
    // V3.2: 根据审计报告V2.1，仅采购员和采购主管可访问采购中心
    Route::middleware('soho_role:procurement_staff|procurement_manager')->group(function () {
        // 采购计划
        Route::middleware('permission:procurement.plan.view')->prefix('procurement/plans')->name('procurement.plans.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementPlanController::class, 'index'])->name('index');
        });

        // 采购申请
        Route::middleware('permission:procurement.request.view')->prefix('procurement/requests')->name('procurement.requests.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementRequestController::class, 'index'])->name('index');
        });

        // 采购订单
        Route::middleware('permission:procurement.order.view')->prefix('procurement/orders')->name('procurement.orders.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementOrderController::class, 'index'])->name('index');
        });

        // 供应商管理
        Route::middleware('permission:supplier.view')->prefix('procurement/suppliers')->name('procurement.suppliers.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\SupplierController::class, 'index'])->name('index');
        });

        // 采购到货
        Route::middleware('permission:procurement.arrival')->prefix('procurement/arrivals')->name('procurement.arrivals.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementArrivalController::class, 'index'])->name('index');
        });

        // 采购入库协同
        Route::middleware('permission:procurement.inbound')->prefix('procurement/inbounds')->name('procurement.inbounds.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementInboundController::class, 'index'])->name('index');
        });

        // 采购审批（仅经理）
        Route::middleware('permission:procurement.approval')->prefix('procurement/approvals')->name('procurement.approvals.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\ProcurementApprovalController::class, 'index'])->name('index');
        });

        // 安全库存
        Route::middleware('permission:inventory.view')->prefix('procurement/safe-stocks')->name('procurement.safe-stocks.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\Procurement\SafeStockController::class, 'index'])->name('index');
        });

        // 采购报表（占位 - 待实现）
        Route::middleware('permission:report.procurement')->get('report/procurement', function () {
            return view('placeholder', ['title' => '采购报表', 'feature' => '采购报表', 'status' => '开发中']);
        })->name('report.procurement');
    });

    // ==================== 物流中心扩展（物流专员） ====================
    Route::middleware('soho_role:logistics_specialist')->group(function () {
        // 面单管理
        Route::middleware('permission:logistics.view')->get('waybill', [\App\Soho\Http\Controllers\Logistics\WaybillController::class, 'index'])->name('waybill.index');

        // 物流轨迹
        Route::middleware('permission:logistics.view')->get('logistics/tracking', [\App\Soho\Http\Controllers\Logistics\LogisticsTrackingController::class, 'index'])->name('logistics.tracking');

        // 异常物流
        Route::middleware('permission:logistics.view')->get('logistics/exception', [\App\Soho\Http\Controllers\Logistics\LogisticsExceptionController::class, 'index'])->name('logistics.exception');

        // 待回传包裹
        Route::middleware('permission:shipment.push')->get('shipment/push/pending', [\App\Soho\Http\Controllers\Fulfillment\ShipmentController::class, 'pushPending'])->name('shipment.push.pending');

        // 回传失败
        Route::middleware('permission:shipment.push')->get('shipment/push/failed', [\App\Soho\Http\Controllers\Fulfillment\ShipmentController::class, 'pushFailed'])->name('shipment.push.failed');

        // 发货回传
        Route::middleware('permission:logistics.callback')->get('logistics/ship-callback', [\App\Soho\Http\Controllers\Fulfillment\ShipmentController::class, 'callback'])->name('logistics.ship.callback');

        // 批量回传
        Route::middleware('permission:logistics.callback')->get('logistics/batch-callback', [\App\Soho\Http\Controllers\Fulfillment\ShipmentController::class, 'batchCallback'])->name('logistics.batch.callback');
    });

    // ==================== 售后中心扩展（售后主管） ====================
    Route::middleware('soho_role:aftersale_manager')->group(function () {
        // 退款审批
        Route::middleware('permission:refund.approve')->get('refund/approve', [\App\Soho\Http\Controllers\Aftersale\RefundApprovalController::class, 'index'])->name('refund.approve');

        // 退货收货
        Route::middleware('permission:return.receive')->get('return/receive', [\App\Soho\Http\Controllers\Aftersale\ReturnReceiveController::class, 'index'])->name('return.receive');

        // 退货质检
        Route::middleware('permission:return.inspect')->get('return/inspect', [\App\Soho\Http\Controllers\Aftersale\ReturnInspectController::class, 'index'])->name('return.inspect');

        // 换货与补发
        Route::middleware('permission:replacement.create')->get('replacement', [\App\Soho\Http\Controllers\Aftersale\ReplacementController::class, 'index'])->name('replacement.create');

        // 售后争议
        Route::middleware('permission:dispute.handle')->get('dispute', [\App\Soho\Http\Controllers\Aftersale\DisputeController::class, 'index'])->name('dispute.handle');
    });

    // ==================== 安全审计（安全管理员/审计员） ====================
    Route::middleware('soho_role:security_admin|auditor')->group(function () {
        // 登录日志 - 只读查看
        Route::middleware('permission:log.audit')->get('log/login', [\App\Soho\Http\Controllers\Security\LoginLogController::class, 'index'])->name('log.login');
        Route::middleware('permission:log.audit')->get('log/login/{log}', [\App\Soho\Http\Controllers\Security\LoginLogController::class, 'show'])->name('log.login.show');
        // 权限变更日志 - 只读查看
        Route::middleware('permission:log.audit')->get('log/permission-change', [\App\Soho\Http\Controllers\Security\PermissionChangeLogController::class, 'index'])->name('log.permission-change');
        Route::middleware('permission:log.audit')->get('log/permission-change/{log}', [\App\Soho\Http\Controllers\Security\PermissionChangeLogController::class, 'show'])->name('log.permission-change.show');
        // 敏感数据访问日志 - 只读查看
        Route::middleware('permission:log.audit')->get('log/sensitive-access', [\App\Soho\Http\Controllers\Security\SensitiveAccessLogController::class, 'index'])->name('log.sensitive-access');
    });

    // ==================== 安全事件（安全管理员/审计员 + 超级管理员） ====================
    // 与 MenuConfig.php security_event 菜单角色对齐
    Route::middleware('soho_role:security_admin|auditor|super_admin')->group(function () {
        // 安全事件 - 查看与处理
        Route::middleware('permission:security.view')->get('security/event', [\App\Soho\Http\Controllers\Security\SecurityEventController::class, 'index'])->name('security.event');
        Route::middleware('permission:security.config')->post('security/event/{event}/resolve', [\App\Soho\Http\Controllers\Security\SecurityEventController::class, 'resolve'])->name('security.event.resolve');
    });

    // ==================== 系统运维扩展（集成运维 + 超级管理员） ====================
    // V3.1：super_admin 可访问系统级日志用于监督
    Route::middleware('soho_role:integration_ops|super_admin')->group(function () {
        Route::middleware('permission:log.api')->get('log/webhook', function () {
            return view('placeholder', ['title' => 'Webhook日志', 'feature' => 'Webhook日志', 'status' => '开发中']);
        })->name('log.webhook');
        Route::middleware('permission:event.replay')->get('event/replay', function () {
            return view('placeholder', ['title' => '事件重放', 'feature' => '事件重放', 'status' => '开发中']);
        })->name('event.replay');
        // 队列监控 - 监视定时任务、队列脚本是否运行
        Route::middleware('permission:system.view')->get('queue/monitor', [\App\Soho\Http\Controllers\System\QueueMonitorController::class, 'index'])->name('queue.monitor');
        // 队列失败任务重试
        Route::middleware('permission:system.config')->post('queue/retry/{id}', [\App\Soho\Http\Controllers\System\QueueMonitorController::class, 'retry'])->name('queue.retry');
        // 队列失败任务删除
        Route::middleware('permission:system.config')->delete('queue/forget/{id}', [\App\Soho\Http\Controllers\System\QueueMonitorController::class, 'forget'])->name('queue.forget');
        // 队列失败任务清空
        Route::middleware('permission:system.config')->post('queue/flush', [\App\Soho\Http\Controllers\System\QueueMonitorController::class, 'flush'])->name('queue.flush');
        // 系统健康检查 - 监视系统状态
        Route::middleware('permission:system.view')->get('system/health', [\App\Soho\Http\Controllers\System\SystemHealthController::class, 'index'])->name('system.health');
        // 系统告警 - 完整CRUD（告警监控、确认、解决、忽略）
        Route::middleware('permission:system.view')->prefix('system/alerts')->name('system.alerts.')->group(function () {
            Route::get('/', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'index'])->name('index');
            Route::get('/{alert}', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'show'])->name('show')->whereNumber('alert');
            Route::post('/{alert}/acknowledge', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'acknowledge'])->name('acknowledge');
            Route::post('/{alert}/resolve', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'resolve'])->name('resolve');
            Route::post('/{alert}/ignore', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'ignore'])->name('ignore');
            Route::post('/batch-acknowledge', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'batchAcknowledge'])->name('batch-acknowledge');
            Route::delete('/{alert}', [\App\Soho\Http\Controllers\System\SystemAlertController::class, 'destroy'])->name('destroy');
        });
    });

    // ==================== 营销中心扩展（营销专员 V3.1新增） ====================
    Route::middleware('soho_role:marketing_staff')->group(function () {
        Route::middleware('permission:promotion.view')->get('promotion/internal', function () {
            return view('placeholder', ['title' => '内部促销', 'feature' => '内部促销', 'status' => '开发中']);
        })->name('promotion.internal');
        Route::middleware('permission:promotion.view')->get('promotion/analysis', function () {
            return view('placeholder', ['title' => '促销分析', 'feature' => '促销分析', 'status' => '开发中']);
        })->name('promotion.analysis');
    });

    // ==================== 集成运维扩展（V3.1遗漏补全） ====================
    Route::middleware('soho_role:integration_ops')->group(function () {
        Route::middleware('permission:sync_task.view')->get('integration/connection/status', function () {
            return view('placeholder', ['title' => '店铺连接状态', 'feature' => '平台店铺连接状态监控', 'status' => '开发中']);
        })->name('integration.connection.status');
        Route::middleware('permission:system.view')->get('integration/api/health', function () {
            return view('placeholder', ['title' => 'API 健康检查', 'feature' => '平台 API 健康状态检查', 'status' => '开发中']);
        })->name('integration.api.health');
        Route::middleware('permission:sync_task.execute')->get('sync-task/failed', function () {
            return view('placeholder', ['title' => '失败任务', 'feature' => '同步失败任务列表', 'status' => '开发中']);
        })->name('sync-task.failed');
        Route::middleware('permission:sync_task.execute')->get('sync-task/retry', function () {
            return view('placeholder', ['title' => '任务重试', 'feature' => '同步任务批量重试', 'status' => '开发中']);
        })->name('sync-task.retry');
    });

    // ==================== Webhook失败记录（集成运维 + 超级管理员） ====================
    Route::middleware('soho_role:integration_ops|super_admin')->group(function () {
        Route::middleware('permission:log.api')->get('log/webhook/failures', function () {
            return view('placeholder', ['title' => 'Webhook 失败记录', 'feature' => 'Webhook 接收失败记录', 'status' => '开发中']);
        })->name('log.webhook.failures');
        // 限流监控：菜单 roles 为 ['integration_ops', 'super_admin']，路由中间件需保持一致
        Route::middleware('permission:system.view')->get('integration/rate-limit', function () {
            return view('placeholder', ['title' => '限流监控', 'feature' => '平台 API 限流监控', 'status' => '开发中']);
        })->name('integration.rate-limit');
    });

    // ==================== 审计追溯中心（V3.1遗漏补全 + super_admin 修复） ====================
    Route::middleware('soho_role:super_admin|security_admin|auditor')->group(function () {
        Route::middleware('permission:log.audit')->get('audit/export-log', [\App\Soho\Http\Controllers\Audit\ExportLogController::class, 'index'])->name('audit.export-log');
        Route::middleware('permission:log.audit')->get('audit/reports', [\App\Soho\Http\Controllers\Audit\AuditReportController::class, 'index'])->name('audit.reports');
    });
    Route::middleware('soho_role:super_admin|auditor')->group(function () {
        Route::middleware('permission:approval_flow.view')->get('audit/approval-flow', [\App\Soho\Http\Controllers\Audit\ApprovalFlowController::class, 'index'])->name('audit.approval-flow');
    });
    // 商品变更审计：审计员 + 超级管理员 + 运营主管
    Route::middleware('soho_role:super_admin|auditor|operation_manager')->group(function () {
        Route::middleware('permission:product.view')->get('audit/product-change', [\App\Soho\Http\Controllers\Audit\ProductChangeController::class, 'index'])->name('audit.product-change');
    });
    // 订单状态审计：审计员 + 超级管理员 + 运营主管
    Route::middleware('soho_role:super_admin|auditor|operation_manager')->group(function () {
        Route::middleware('permission:order.view')->get('audit/order-status', [\App\Soho\Http\Controllers\Audit\OrderStatusController::class, 'index'])->name('audit.order-status');
    });
    // 对账差异审计：审计员 + 超级管理员 + 运营主管
    Route::middleware('soho_role:super_admin|auditor|operation_manager')->group(function () {
        Route::middleware('permission:finance.reconcile')->get('audit/reconciliation-diff', [\App\Soho\Http\Controllers\Audit\ReconciliationDiffController::class, 'index'])->name('audit.reconciliation-diff');
    });

    // ==================== 运营主管扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:operation_manager')->group(function () {
        // 运营主管仪表盘与经营报表
        Route::middleware('permission:report.sales')->get('operation/dashboard', [\App\Soho\Http\Controllers\Report\OperationDashboardController::class, 'index'])->name('operation.dashboard');
        Route::middleware('permission:shop.view')->get('operation/shop-overview', [\App\Soho\Http\Controllers\Report\ShopOverviewController::class, 'index'])->name('operation.shop.overview');
        Route::middleware('permission:order.view')->get('operation/ship-on-time', [\App\Soho\Http\Controllers\Report\ShipOnTimeController::class, 'index'])->name('operation.ship.on-time');
        // 注：商品发布审批、调价审批、订单取消审批、库存调整审批、售后退款审批
        // 已统一在下方"统一审批中心"路由组中以控制器实现，路由名为 approval.*-*.index 形式
    });

    // 运营主管扩展路由 - 售后报表
    Route::middleware('soho_role:operation_manager|data_analyst')->group(function () {
        Route::middleware('permission:report.aftersale')->get('report/aftersale', [\App\Soho\Http\Controllers\Report\AftersaleReportController::class, 'index'])->name('report.aftersale');
    });
    // 运营主管扩展路由 - 库存预警
    Route::middleware('soho_role:operation_manager')->group(function () {
        Route::middleware('permission:inventory.view')->get('operation/inventory-warning', [\App\Soho\Http\Controllers\Report\InventoryWarningController::class, 'index'])->name('operation.inventory.warning');
    });
    // 运营主管扩展路由 - 售后率分析
    Route::middleware('soho_role:operation_manager')->group(function () {
        Route::middleware('permission:report.aftersale')->get('operation/aftersale-rate', [\App\Soho\Http\Controllers\Report\AftersaleRateController::class, 'index'])->name('operation.aftersale.rate');
    });
    // 运营主管扩展路由 - 异常订单概览
    Route::middleware('soho_role:operation_manager')->group(function () {
        Route::middleware('permission:order.view')->get('operation/order-exception', [\App\Soho\Http\Controllers\Report\OrderExceptionController::class, 'index'])->name('operation.order.exception');
    });

    // ==================== 平台运营扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:platform_operator')->group(function () {
        Route::middleware('permission:product.view')->get('platform/listing', [\App\Soho\Http\Controllers\Platform\PlatformBoardController::class, 'listing'])->name('platform.listing');
        Route::middleware('permission:product.create')->match(['get', 'post'], 'platform/product-publish', [\App\Soho\Http\Controllers\Platform\PlatformBoardController::class, 'productPublish'])->name('platform.product.publish');
        Route::middleware('permission:product.view')->get('platform/publish-task', [\App\Soho\Http\Controllers\Platform\PlatformBoardController::class, 'publishTask'])->name('platform.publish.task');
        Route::middleware('permission:category.view')->get('platform/category-mapping', [\App\Soho\Http\Controllers\Platform\PlatformBoardController::class, 'categoryMapping'])->name('platform.category.mapping');
        Route::middleware('permission:product.attribute.view')->get('platform/attribute', [\App\Soho\Http\Controllers\Platform\PlatformBoardController::class, 'attribute'])->name('platform.attribute');
    });

    // ==================== 商品管理员扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:product_admin|product_reviewer')->group(function () {
        Route::middleware('permission:sku.view')->get('product/sku', [\App\Soho\Http\Controllers\Product\SkuController::class, 'index'])->name('product.sku');
        Route::middleware('permission:product.view')->get('product/versions', [\App\Soho\Http\Controllers\Product\ProductVersionController::class, 'index'])->name('product.versions');
    });
    Route::middleware('soho_role:product_admin')->group(function () {
        Route::middleware('permission:product.create')->match(['get', 'post'], 'product/import', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'import'])->name('product.import');
        Route::middleware('permission:product.create')->get('product/import/template', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'importTemplate'])->name('product.import.template');
        Route::middleware('permission:product.view')->get('product/tasks/pending-edit', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'tasksPendingEdit'])->name('product.tasks.pending-edit');
        Route::middleware('permission:product.view')->get('product/tasks/returned', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'tasksReturned'])->name('product.tasks.returned');
        Route::middleware('permission:product.view')->get('product/tasks/exception', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'tasksException'])->name('product.tasks.exception');
    });

    // ==================== 商品审核员扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:product_reviewer')->group(function () {
        Route::middleware('permission:product.review')->get('product/review/pending', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'reviewPending'])->name('product.review.pending');
        Route::middleware('permission:product.view')->get('product/review/record', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'reviewRecord'])->name('product.review.record');
        Route::middleware('permission:product.review')->get('product/review/change', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'reviewChange'])->name('product.review.change');
        Route::middleware('permission:product.view')->get('product/spu/detail', [\App\Soho\Http\Controllers\Product\ProductBoardController::class, 'spuDetail'])->name('product.spu.detail');
    });

    // ==================== 订单主管扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:order_manager')->group(function () {
        Route::middleware('permission:order.view')->get('order/pending-fulfill', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'pendingFulfill'])->name('order.pending-fulfill');
        Route::middleware('permission:order.view')->post('order/pending-fulfill/store', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'storeFulfillment'])->name('order.pending-fulfill.store');
        Route::middleware('permission:order.edit')->get('order/split', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'split'])->name('order.split');
        Route::middleware('permission:order.edit')->post('order/split/apply', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'applySplit'])->name('order.split.apply');
        Route::middleware('permission:order.edit')->get('order/merge', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'merge'])->name('order.merge');
        Route::middleware('permission:order.edit')->post('order/merge/apply', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'applyMerge'])->name('order.merge.apply');
        Route::middleware('permission:order.view')->get('order/export', [App\Soho\Http\Controllers\Order\OrderOperationController::class, 'export'])->name('order.export');
        Route::middleware('permission:order.edit')->get('order/warehouse-assign', [App\Soho\Http\Controllers\Order\OrderOperationController::class, 'warehouseAssign'])->name('order.warehouse.assign');
        Route::middleware('permission:order.edit')->post('order/warehouse-assign/apply', [App\Soho\Http\Controllers\Order\OrderOperationController::class, 'applyWarehouseAssign'])->name('order.warehouse.assign.apply');
        Route::middleware('permission:order.view')->get('order/fulfillment', [App\Soho\Http\Controllers\Order\OrderOperationController::class, 'fulfillments'])->name('order.fulfillment');
        Route::middleware('permission:order.view')->get('order/stock-shortage', [\App\Soho\Http\Controllers\Order\OrderOperationController::class, 'shortage'])->name('order.stock-shortage');
    });

    // ==================== 客服扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:customer_service')->group(function () {
        Route::middleware('permission:customer.view')->get('customer/tags', [\App\Soho\Http\Controllers\Customer\CustomerTagController::class, 'index'])->name('customer.tags');
        Route::middleware('permission:customer.edit')->post('customer/tags', [\App\Soho\Http\Controllers\Customer\CustomerTagController::class, 'store'])->name('customer.tags.store');
        Route::middleware('permission:customer.edit')->put('customer/tags/{tag}', [\App\Soho\Http\Controllers\Customer\CustomerTagController::class, 'update'])->name('customer.tags.update');
        Route::middleware('permission:customer.edit')->delete('customer/tags/{tag}', [\App\Soho\Http\Controllers\Customer\CustomerTagController::class, 'destroy'])->name('customer.tags.destroy');
    });
    Route::middleware('soho_role:customer_service')->group(function () {
        Route::middleware('permission:customer.view')->get('customer/privacy', function () {
            return view('placeholder', ['title' => '客户隐私请求', 'feature' => '客户隐私请求处理', 'status' => '开发中']);
        })->name('customer.privacy');
        Route::middleware('permission:after_sale.view')->get('aftersale/apply', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'apply'])->name('aftersale.apply');
        Route::middleware('permission:after_sale.view')->post('aftersale/apply/store', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'store'])->name('aftersale.apply.store');
        Route::middleware('permission:after_sale.view')->get('aftersale/supplement', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'supplement'])->name('aftersale.supplement');
        Route::middleware('permission:after_sale.view')->post('aftersale/supplement/save', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'saveSupplement'])->name('aftersale.supplement.save');
        Route::middleware('permission:after_sale.view')->get('aftersale/progress', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'progress'])->name('aftersale.progress');
        Route::middleware('permission:after_sale.view')->get('aftersale/communication', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'communication'])->name('aftersale.communication');
        Route::middleware('permission:after_sale.view')->post('aftersale/communication/store', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'storeCommunication'])->name('aftersale.communication.store');
    });

    // ==================== 仓库主管扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:warehouse_manager')->group(function () {
        Route::middleware('permission:warehouse.view')->get('warehouse/zones', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'zones'])->name('warehouse.zones');
        Route::middleware('permission:warehouse.view')->get('warehouse/locations', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'locations'])->name('warehouse.locations');
        Route::middleware('permission:inventory.view')->get('warehouse/stock-warning', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'stockWarning'])->name('warehouse.stock-warning');
        Route::middleware('permission:inventory.adjust')->get('warehouse/inbound', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'inbound'])->name('warehouse.inbound');
        Route::middleware('permission:inventory.adjust')->get('warehouse/outbound', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'outboundDocs'])->name('warehouse.outbound');
        Route::middleware('permission:inventory.stocktake')->get('warehouse/stocktake', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'stocktakeDocs'])->name('warehouse.stocktake');
        Route::middleware('permission:inventory.adjust')->get('warehouse/stock-adjust', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'adjustDocs'])->name('warehouse.stock-adjust');
        Route::middleware('permission:inventory.view')->get('warehouse/inventory-doc', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'inventoryDoc'])->name('warehouse.inventory-doc');
        Route::middleware('permission:return.receive')->get('warehouse/return-receive', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'returnReceive'])->name('warehouse.return.receive');
        Route::middleware('permission:return.inspect')->get('warehouse/return-inspect', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'returnInspect'])->name('warehouse.return.inspect');
    });
    Route::middleware('soho_role:warehouse_manager|data_analyst')->group(function () {
        Route::middleware('permission:report.stock')->get('warehouse/inventory-turnover', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'inventoryTurnover'])->name('warehouse.inventory.turnover');
        Route::middleware('permission:report.stock')->get('warehouse/stock-age', [\App\Soho\Http\Controllers\Warehouse\WarehouseBoardController::class, 'stockAge'])->name('warehouse.stock.age');
    });
    Route::middleware('soho_role:warehouse_manager|order_manager')->group(function () {
        // 待分配作业
        Route::middleware('permission:fulfillment.view')->get('fulfillment/assignments', [\App\Soho\Http\Controllers\Fulfillment\FulfillmentAssignmentController::class, 'index'])->name('fulfillment.assignments.index');
    });

    // ==================== 仓库岗位路由（已移至履约作业路由组） ====================
    // 注：pick.tasks.*, check.tasks.*, pack.tasks.* 路由已在上方履约作业区块定义

    // ==================== 物流专员扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:logistics_specialist')->group(function () {
        // 物流账单
        Route::middleware('permission:logistics.bill.view')->get('logistics/bill', [\App\Soho\Http\Controllers\Logistics\LogisticsBillController::class, 'index'])->name('logistics.bill');

        // 发货及时率
        Route::middleware('permission:logistics.view')->get('logistics/on-time', [\App\Soho\Http\Controllers\Logistics\LogisticsMetricsController::class, 'index'])->name('logistics.on-time');

        // 物流时效
        Route::middleware('permission:logistics.view')->get('logistics/lead-time', [\App\Soho\Http\Controllers\Logistics\LogisticsMetricsController::class, 'index'])->name('logistics.lead-time');
    });

    // ==================== 售后主管扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:aftersale_manager')->group(function () {
        // 换货与补发（复用 ReplacementController）
        Route::middleware('permission:after_sale.handle')->get('aftersale/exchange', [\App\Soho\Http\Controllers\Aftersale\ReplacementController::class, 'index'])->name('aftersale.exchange');

        // 售后争议（复用 DisputeController）
        Route::middleware('permission:after_sale.handle')->get('aftersale/dispute', [\App\Soho\Http\Controllers\Aftersale\DisputeController::class, 'index'])->name('aftersale.dispute');

        // 售后操作记录（占位 - 待实现）
        Route::middleware('permission:after_sale.view')->get('aftersale/operation-log', [\App\Soho\Http\Controllers\Aftersale\AfterSaleBoardController::class, 'operationLog'])->name('aftersale.operation.log');
    });

    // ==================== 财务人员/财务主管扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:finance_staff|finance_manager')->group(function () {
        Route::middleware('permission:finance.view')->get('finance/refund-flow', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'refundFlow'])->name('finance.refund.flow');
        Route::middleware('permission:finance.settle')->get('finance/platform-settlement', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'platformSettlement'])->name('finance.platform.settlement');
        Route::middleware('permission:finance.view')->get('finance/settlement-detail', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'settlementDetail'])->name('finance.settlement.detail');
        Route::middleware('permission:finance.view')->get('finance/cost-allocation', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'costAllocation'])->name('finance.cost.allocation');
        Route::middleware('permission:finance.reconcile')->get('finance/reconcile-task', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'reconcileTask'])->name('finance.reconcile.task');
    });
    Route::middleware('soho_role:finance_manager')->group(function () {
        Route::middleware('permission:finance.settle')->get('finance/settlement-audit', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'settlementAudit'])->name('finance.settlement.audit');
        Route::middleware('permission:finance.view')->get('finance/cost-audit', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'costAudit'])->name('finance.cost.audit');
        Route::middleware('permission:finance.reconcile')->get('finance/diff-approval', [\App\Soho\Http\Controllers\Finance\FinanceBoardController::class, 'diffApproval'])->name('finance.diff.approval');
    });

    // ==================== 采购员/供应链主管扩展路由（已移至采购中心主路由组） ====================
    // 注：procurement.order.detail, procurement.safe-stock, procurement.in-transit 等路由
    // 已在上方采购中心区块以 procurement.orders.index, procurement.safe-stocks.index 等形式实现

    // ==================== 数据分析员扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:data_analyst')->group(function () {
        Route::middleware('permission:report.sales')->get('report/order', [\App\Soho\Http\Controllers\Report\OrderReportController::class, 'index'])->name('report.order');
        Route::middleware('permission:report.aftersale')->get('report/aftersale-detail', [\App\Soho\Http\Controllers\Report\AftersaleDetailController::class, 'index'])->name('report.aftersale-detail');
        Route::middleware('permission:report.sales')->get('report/shop-analysis', [\App\Soho\Http\Controllers\Report\ShopAnalysisController::class, 'index'])->name('report.shop.analysis');
        Route::middleware('permission:report.sales')->get('report/sku-analysis', [\App\Soho\Http\Controllers\Report\SkuAnalysisController::class, 'index'])->name('report.sku.analysis');
        Route::middleware('permission:report.stock')->get('report/inventory-turnover', [\App\Soho\Http\Controllers\Report\InventoryTurnoverController::class, 'index'])->name('report.inventory.turnover');
        Route::middleware('permission:report.sales')->get('report/metrics-definition', [\App\Soho\Http\Controllers\Report\MetricsDefinitionController::class, 'index'])->name('report.metrics.definition');
    });

    // ==================== 只读用户扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:read_only')->group(function () {
        Route::middleware('permission:report.sales')->get('readonly/order-report', [\App\Soho\Http\Controllers\Report\ReadonlyController::class, 'orderReport'])->name('readonly.order.report');
        Route::middleware('permission:product.view')->get('readonly/listing-view', [\App\Soho\Http\Controllers\Report\ReadonlyController::class, 'listingView'])->name('readonly.listing.view');
        Route::middleware('permission:inventory.view')->get('readonly/inventory-warning', [\App\Soho\Http\Controllers\Report\ReadonlyController::class, 'inventoryWarning'])->name('readonly.inventory.warning');
    });

    // ==================== 营销专员扩展路由（V3.1补全） ====================
    Route::middleware('soho_role:marketing_staff')->group(function () {
        Route::middleware('permission:promotion.edit')->get('marketing/campaign-join', function () {
            return view('placeholder', ['title' => '活动报名', 'feature' => '平台活动报名', 'status' => '开发中']);
        })->name('marketing.campaign.join');
        Route::middleware('permission:promotion.edit')->get('marketing/campaign-rule', function () {
            return view('placeholder', ['title' => '活动规则', 'feature' => '活动规则管理', 'status' => '开发中']);
        })->name('marketing.campaign.rule');
    });

    // ==================== 统一审批中心（各业务模块审批统一入口） ====================
    // 修复 P0：在 role 粗粒度守卫基础上，为每个子路由补充对应 permission 细粒度守卫
    // 防止无对应审批权限的角色越权访问（如 finance_staff 访问商品发布审批）
    Route::middleware('soho_role:super_admin|tenant_admin|operation_manager|product_admin|product_reviewer|order_manager|aftersale_manager|warehouse_manager|finance_manager|finance_staff')->prefix('approval')->name('approval.')->group(function () {
        // 商品发布审批（需 product.approve 权限）
        Route::get('product-publish', [\App\Soho\Http\Controllers\Approval\ProductPublishApprovalController::class, 'index'])
            ->middleware('permission:product.approve')
            ->name('product-publish.index');
        // 调价审批（统一审批中心入口，与价格中心独立控制器并存，需 price.change.approve 权限）
        Route::get('price-change', [\App\Soho\Http\Controllers\Approval\PriceChangeApprovalController::class, 'index'])
            ->middleware('permission:price.change.approve')
            ->name('price-change.index');
        // 订单取消审批（需 order.approve 权限）
        Route::get('order-cancel', [\App\Soho\Http\Controllers\Approval\OrderCancelApprovalController::class, 'index'])
            ->middleware('permission:order.approve')
            ->name('order-cancel.index');
        // 库存调整审批（需 inventory.approve 权限）
        Route::get('inventory-adjust', [\App\Soho\Http\Controllers\Approval\InventoryAdjustApprovalController::class, 'index'])
            ->middleware('permission:inventory.approve')
            ->name('inventory-adjust.index');
        // 退款审批中心（统一入口，与售后模块独立控制器并存，需 refund.approve 权限）
        Route::get('refund', [\App\Soho\Http\Controllers\Approval\RefundApprovalCenterController::class, 'index'])
            ->middleware('permission:refund.approve')
            ->name('refund.index');
        // 大额差异审批（需 finance.reconcile 权限）
        Route::get('finance-diff', [\App\Soho\Http\Controllers\Approval\FinanceDiffApprovalController::class, 'index'])
            ->middleware('permission:finance.reconcile')
            ->name('finance-diff.index');
    });

    // ==================== 未授权页面 ====================
    Route::get('unauthorized', function () {
        return view('errors.403');
    })->name('unauthorized');
});
});
