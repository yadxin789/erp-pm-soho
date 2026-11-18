<?php

namespace App\Soho\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SOHO 角色校验：无权限不返回 403，统一收敛到工作台并给出提示（入口本身由菜单隐藏）。
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

        foreach ($roles as $role) {
            foreach (explode('|', $role) as $r) {
                if ($user->hasRole(trim($r))) {
                    return $next($request);
                }
            }
        }

        return redirect()->route('soho.dashboard')->with('error', '您当前角色无权访问该页面。');
    }
}
