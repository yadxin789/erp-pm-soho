<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\ModuleContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 设置模块上下文（T0-04）
 *
 * 注册别名：'module'（见 bootstrap/app.php）
 * 用法：Route::middleware('module:erp')  →  强制 erp
 *       Route::middleware('module')      →  自动推断
 */
class SetModuleContext
{
    public function handle(Request $request, Closure $next, ?string $module = null): Response
    {
        $resolved = $module ?: $this->resolve($request);

        if ($resolved !== null && in_array($resolved, ModuleContext::all(), true)) {
            ModuleContext::set($resolved);

            // 记住用户最近访问的模块，供门户/回跳使用（仅 HTML 请求）
            if ($request->isMethod('GET') && ! $request->expectsJson()) {
                $request->session()->put(config('modules.context.session_key', 'active_module'), $resolved);
            }
        }

        try {
            return $next($request);
        } finally {
            ModuleContext::clear();
        }
    }

    protected function resolve(Request $request): ?string
    {
        // 1) 显式请求头（API / 自动化测试）
        $header = $request->header(config('modules.context.header', 'X-Module'));
        if (is_string($header) && $header !== '') {
            return $header;
        }

        // 2) session 中的最近模块
        $session = $request->hasSession()
            ? $request->session()->get(config('modules.context.session_key', 'active_module'))
            : null;
        if (is_string($session) && $session !== '') {
            return $session;
        }

        // 3) URL 首段
        $first = $request->segment(1);
        if (is_string($first) && in_array($first, ModuleContext::all(), true)) {
            return $first;
        }

        return null;
    }
}
