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
 * 无权访问统一 403，文案中文、不含内部标识。
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

            return $next($request);
        }

        abort(403, '您当前没有该模块的访问权限，请联系管理员开通。');
    }
}
