<?php

namespace App\Core\Http\Controllers;

use App\Core\Menu\MenuRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 菜单预览与自检（TC-08）。
 *
 * GET /core/menu             → 当前模块（session active_module 或用户模块）菜单
 * GET /core/menu/{module}    → 指定模块菜单（需通过 module.access 准入）
 *
 * 页面同时给出「基线条数 / 当前用户可见条数 / 路由未迁入条数」，用真实渲染替代口头声明。
 */
class MenuPreviewController extends Controller
{
    public function show(Request $request, ?string $module = null)
    {
        $module = $module ?: ($request->session()->get('active_module') ?: config('modules.default'));

        abort_unless(array_key_exists($module, config('modules.modules', [])), 404, '未知模块：'.$module);

        // 非当前用户可进入的模块，这里显式拦截（预览页不走 module.access 组，避免动态模块名无法直接挂中间件）
        $user = $request->user();
        abort_unless($user && $user->canEnterModule($module), 403, '您当前没有该模块的访问权限，请联系管理员开通。');
        $request->session()->put('active_module', $module);

        $all = MenuRepository::all($module);
        $visible = MenuRepository::visible($module, $user);
        $groups = MenuRepository::grouped($visible);

        $pending = [];
        foreach ($visible as $item) {
            if (! MenuRepository::routeExists($item['route'])) {
                $pending[] = $item;
            }
        }

        return view('core::menu.preview', [
            'module' => $module,
            'total' => count($all),
            'visibleCount' => count($visible),
            'hiddenCount' => count($all) - count($visible),
            'groups' => $groups,
            'pending' => $pending,
            'baseline' => json_decode((string) @file_get_contents(base_path('docs/menu/baseline-menu.json')), true) ?: [],
        ]);
    }
}
