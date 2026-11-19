<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\ModuleContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 权限点校验（TC-07 配套）。
 *
 * 用法：->middleware('permission:erp.sales_orders.view')
 * 判定走 spatie（`$user->can()`），权限名必须带模块前缀。
 * 模块路由迁入时逐组挂载（B-02/B-03/B-04），当前仅注册别名。
 *
 * 不返回 403（2026-11-17 口径）：无权限入口在页面里就不渲染，直接敲 URL 也只会被送回原页并提示。
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest('/login');
        }

        $allowed = false;
        foreach (permission_candidates($permission) as $candidate) {
            if ($user->can($candidate)) {
                $allowed = true;
                break;
            }
        }

        if ($user->hasRole('super_admin') || $allowed) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403);
        }

        $home = url('/');
        try {
            $home = match (ModuleContext::id()) {
                'pm' => route('pm.dashboard'),
                'soho' => route('soho.dashboard'),
                'erp' => class_exists(\App\Erp\Support\RoleScope::class)
                    ? \App\Erp\Support\RoleScope::home($user)
                    : route('app.dashboard'),
                default => url('/'),
            };
        } catch (\Throwable) {
            $home = url('/');
        }

        return redirect()->to($home)->with('warning', '当前账号没有该功能权限，已返回工作台。');
    }
}
