<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 角色校验中间件（替代 Spatie RoleMiddleware）。
 *
 * 口径（2026-11-17 用户要求）：不存在 403 页。无权限用户本来在菜单里就看不到入口，
 * 若直接敲 URL 访问，则送回其所属模块首页并给出提示，而不是抛 403。
 *
 * 用法与 Spatie 一致：->middleware('role:admin|sales_manager')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest('/login');
        }

        $allowed = array_filter(array_map('trim', explode('|', $roles)));

        foreach ($allowed as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // API/JSON 请求保持机器可读语义
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => '无权限访问该资源'], 403);
        }

        $fallback = $this->fallbackUrl($user);

        if ($request->fullUrl() === $fallback) {
            return $next($request);
        }

        return redirect()->to($fallback)->with('warning', '当前账号没有该功能权限，已返回工作台。');
    }

    private function fallbackUrl($user): string
    {
        $module = $user->module ?: 'erp';

        $routeName = match ($module) {
            'pm'   => 'pm.dashboard',
            'soho' => 'soho.dashboard',
            default => 'admin.dashboard',
        };

        try {
            return route($routeName);
        } catch (\Throwable $e) {
            return url('/');
        }
    }
}
