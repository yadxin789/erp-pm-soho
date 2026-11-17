<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 研发项目管理 路由（URL 前缀 /pm，路由名前缀 pm.）
|--------------------------------------------------------------------------
| 迁入原项目路由时：
|   1) 保留原 URL 后缀（例如原 /app/sales/orders → /pm/app/sales/orders）
|   2) 所有 ->name() 必须加 "pm." 前缀（防跨模块重名）
|   3) 模块专用中间件挂在本组内，禁止全局 append
*/

Route::middleware(['web', 'auth', 'module:pm', 'module.access:pm'])
    ->prefix('pm')
    ->name('pm.')
    ->group(function () {

        Route::get('/', fn () => response()->json([
            'module'  => 'pm',
            'context' => \App\Core\Support\ModuleContext::current(),
            'status'  => '骨架就绪，业务路由待迁入',
        ]))->name('home');

    });