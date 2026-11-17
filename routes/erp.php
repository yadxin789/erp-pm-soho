<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ERP 电子厂管理 路由（URL 前缀 /erp，路由名前缀 erp.）
|--------------------------------------------------------------------------
| 迁入原项目路由时：
|   1) 保留原 URL 后缀（例如原 /app/sales/orders → /erp/app/sales/orders）
|   2) 所有 ->name() 必须加 "erp." 前缀（防跨模块重名）
|   3) 模块专用中间件挂在本组内，禁止全局 append
*/

Route::middleware(['web', 'auth', 'module:erp', 'module.access:erp'])
    ->prefix('erp')
    ->name('erp.')
    ->group(function () {

        Route::get('/', function () { return redirect()->route('core.home')->with('warning', 'ERP 模块业务菜单正在迁入（B-02/B-04），先回到统一门户。'); })->name('home');

    });