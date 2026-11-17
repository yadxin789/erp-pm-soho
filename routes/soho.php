<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 多平台电商管理 路由（URL 前缀 /soho，路由名前缀 soho.）
|--------------------------------------------------------------------------
| 迁入原项目路由时：
|   1) 保留原 URL 后缀（例如原 /app/sales/orders → /soho/app/sales/orders）
|   2) 所有 ->name() 必须加 "soho." 前缀（防跨模块重名）
|   3) 模块专用中间件挂在本组内，禁止全局 append
*/

Route::middleware(['web', 'auth', 'module:soho', 'module.access:soho'])
    ->prefix('soho')
    ->name('soho.')
    ->group(function () {

        Route::get('/', function () { return redirect()->route('core.home')->with('warning', 'SOHO 模块业务菜单正在迁入（B-02/B-04），先回到统一门户。'); })->name('home');

    });