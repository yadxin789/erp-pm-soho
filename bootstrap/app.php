<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // T0-04：模块上下文中间件（路由组用 module:erp / module:pm / module:soho）
        $middleware->alias([
            'module' => \App\Core\Http\Middleware\SetModuleContext::class,
        ]);

        // 注意：erp 的 EnsureRoleScope、soho 的 LogUserActivity 一律不得全局 append，
        // 必须在各自模块路由组内挂载（见 B-02 TE-07 / B-04 TS-15）。
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
