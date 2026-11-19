<?php

namespace App\Soho\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SOHO 角色校验：无权限不返回 403，统一收敛到工作台并给出提示（入口本身由菜单隐藏）。
 *
 * 角色名兼容两种命名：裸名（`warehouse_manager`）与模块前缀名（`soho.warehouse_manager`）。
 * 两者在库中并存且权限点分别落在裸名/前缀名两套体系里，判定时必须同时接受，否则同一岗位
 * 因角色命名不同会出现「有权限却进不去」。
 */
class SohoRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest('/login');
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // 若该路由同时挂了权限点校验（permission:xxx），则由权限点做唯一裁决，
        // 角色白名单不再二次拦截——否则会出现「权限已授予、角色却在白名单之外」而 302 的假拒绝。
        foreach ($request->route()?->gatherMiddleware() ?? [] as $mw) {
            if (! is_string($mw)) {
                continue;
            }
            if (str_contains($mw, 'CheckPermission') || str_starts_with($mw, 'permission:')) {
                return $next($request);
            }
        }

        foreach ($roles as $role) {
            foreach (explode('|', $role) as $r) {
                $name = trim($r);
                if ($name === '') {
                    continue;
                }

                foreach ([$name, 'soho.' . $name] as $candidate) {
                    if ($user->hasRole($candidate)) {
                        return $next($request);
                    }
                }
            }
        }

        return redirect()->route('soho.dashboard')->with('error', '您当前角色无权访问该页面。');
    }
}
