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
    // T0-06：模块命令目录注册（Laravel 12 只自动发现 app/Console/Commands）
    ->withCommands([
        __DIR__.'/../app/Core/Console/Commands',
        __DIR__.'/../app/Erp/Console/Commands',
        __DIR__.'/../app/Pm/Console/Commands',
        __DIR__.'/../app/Soho/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // 统一登录入口：未登录访客一律回 /login（默认会去找名为 login 的路由，本项目为 core.login）
        $middleware->redirectGuestsTo(fn () => route('core.login'));

        // T0-04：模块上下文中间件（路由组用 module:erp / module:pm / module:soho）
        $middleware->alias([
            'module'        => \App\Core\Http\Middleware\SetModuleContext::class,
            'module.access' => \App\Core\Http\Middleware\EnsureModuleAccess::class,
        ]);

        // 注意：erp 的 EnsureRoleScope、soho 的 LogUserActivity 一律不得全局 append，
        // 必须在各自模块路由组内挂载（见 B-02 TE-07 / B-04 TS-15）。
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
