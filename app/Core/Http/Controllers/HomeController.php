<?php

namespace App\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 登录后的分流点（TC-04）。
 *
 * 单业务模块 → 直接进该模块根路由；多模块或无业务模块 → 回门户选卡片。
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        $modules = array_values(array_diff($user->resolveModules(), ['core']));

        if (count($modules) === 1) {
            $request->session()->put('active_module', $modules[0]);

            return redirect('/'.$modules[0]);
        }

        $request->session()->put('active_module', 'core');

        return redirect('/');
    }
}
