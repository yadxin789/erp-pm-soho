<?php

namespace App\Core\Http\Middleware;

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

        if ($user->can($permission)) {
            return $next($request);
        }

        abort_unless($user->can($permission), 404);
    }
}
