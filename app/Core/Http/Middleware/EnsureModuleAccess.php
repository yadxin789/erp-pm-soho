<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 模块准入（TC-04 / D15）。
 *
 * 用法：路由组 ->middleware('module.access:erp')
 * 判定链：users.module → user_module_access（未过期）→ departments.module → core
 * 无权访问不回 403（2026-11-17 用户口径：不该存在 403 页面），改为回门户 + 提示：
 * 入口本身按权限隐藏，直接敲 URL 只会被温柔地送回门户，不暴露「有这个页面」。
 */
class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest('/login');
        }

        if ($module === 'core' || $user->canEnterModule($module)) {
            $request->session()->put('active_module', $module);
            $request->session()->put('last_module', $module);

            return $next($request);
        }

        $allowed = array_values(array_diff($user->resolveModules(), ['core']));

        if ($allowed) {
            return redirect()->route('core.home')
                ->with('warning', '当前账号未开通「'.strtoupper($module).'」模块，已跳回门户。');
        }

        return redirect()->route('core.home')
            ->with('warning', '当前账号还没有分配业务模块，请联系管理员开通。');
    }
}
