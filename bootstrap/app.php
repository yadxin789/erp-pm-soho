<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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

        // TC-12：OnlyOffice 保存回调来自文档服务器，没有会话与 CSRF token，靠 document key 校验
        $middleware->validateCsrfTokens(except: [
            'files/onlyoffice/callback/*',
        ]);

        // T0-04：模块上下文中间件（路由组用 module:erp / module:pm / module:soho）
        $middleware->alias([
            'module'        => \App\Core\Http\Middleware\SetModuleContext::class,
            'module.access' => \App\Core\Http\Middleware\EnsureModuleAccess::class,
            'permission'    => \App\Core\Http\Middleware\CheckPermission::class,
        'soho_role'     => \App\Soho\Http\Middleware\SohoRoleMiddleware::class,
            'log.activity'  => \App\Soho\Http\Middleware\LogUserActivity::class,
        'write.guard'   => \App\Soho\Http\Middleware\EnsureWritePermission::class,
            // ERP 原路由组使用 Spatie 的 role:xxx / role_or_permission:xxx
            // 口径：不做 403 页，无权限直接访问 URL 时回模块首页并提示（App\Core\Http\Middleware\CheckRole）
            'role'               => \App\Core\Http\Middleware\CheckRole::class,
        'role_scope'         => \App\Erp\Http\Middleware\EnsureRoleScope::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // 注意：erp 的 EnsureRoleScope、soho 的 LogUserActivity 一律不得全局 append，
        // 必须在各自模块路由组内挂载（见 B-02 TE-07 / B-04 TS-15）。
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 口径（用户要求）：不存在 403 页面。
        // 授权失败（Policy/Gate/abort(403)）在网页请求下回落到用户所属模块首页并提示；
        // API / JSON 请求仍保持 403 语义。
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $user = $request->user();
            $home = '/';

            try {
                $home = match ($user?->module) {
                    'pm'   => route('pm.dashboard'),
                    'soho' => route('soho.dashboard'),
                    'erp'  => \App\Erp\Support\RoleScope::home($user),
                    default => url('/'),
                };
            } catch (\Throwable) {
                $home = url('/');
            }

            if ($request->fullUrl() === $home) {
                return null;
            }

            return redirect()->to($home)->with('warning', '当前账号没有该功能权限，已返回工作台。');
        });

        // 兼容 abort(403) 的通用写法
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $user = $request->user();

            try {
                $home = match ($user?->module) {
                    'pm'   => route('pm.dashboard'),
                    'soho' => route('soho.dashboard'),
                    'erp'  => \App\Erp\Support\RoleScope::home($user),
                    default => url('/'),
                };
            } catch (\Throwable) {
                $home = url('/');
            }

            if ($request->fullUrl() === $home) {
                return null;
            }

            return redirect()->to($home)->with('warning', '当前账号没有该功能权限，已返回工作台。');
        });
    })->create();

