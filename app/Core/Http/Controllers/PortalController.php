<?php

namespace App\Core\Http\Controllers;

use App\Core\Support\ModuleContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 门户首页（TC-05）：三张模块卡片。
 *
 * 规则：
 * - 卡片清单取 config('modules.portal.cards')，不硬编码模块名；
 * - 未登录：卡片可点，auth 中间件会把 intended 记下，登录后直达该模块；
 * - 已登录：无权限模块置灰 + "无权限，请联系管理员"；可进入模块显示上次进入标记。
 */
class PortalController extends Controller
{
    /** 卡片文案（模块标识 → 名称/简述），模块本身仍在 config/modules.php 注册 */
    private const COPY = [
        'erp' => [
            'name' => 'ERP 电子厂管理',
            'desc' => '销售、采购、生产、库存、质检、物流，以及人事与财务基础台账。',
            'cta'  => '进入 ERP',
        ],
        'pm' => [
            'name' => '研发项目管理',
            'desc' => '项目立项、任务分解、甘特与协同、文件审核流、知识库与 AI 工作台。',
            'cta'  => '进入 PM',
        ],
        'soho' => [
            'name' => '多平台电商管理',
            'desc' => '多店铺订单、商品与平台 SKU、库存同步、采购履约、价格与售后、财务报表。',
            'cta'  => '进入 SOHO',
        ],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $lastModule = $request->session()->get('last_module');

        $cards = [];
        foreach ((array) config('modules.portal.cards', array_keys(self::COPY)) as $module) {
            if (! ModuleContext::exists($module)) {
                continue;
            }

            $copy = self::COPY[$module] ?? [
                'name' => config("modules.modules.{$module}.label", strtoupper($module)),
                'desc' => '',
                'cta'  => '进入 '.strtoupper($module),
            ];

            $cards[] = [
                'module'    => $module,
                'name'      => $copy['name'],
                'desc'      => $copy['desc'],
                'cta'       => $copy['cta'],
                'href'      => '/'.$module,
                // 未登录不判定权限（登录后由 module.access 兜底 403）
                // 超级管理员门户始终展示三个模块卡片，便于统一入口演示与切换
                'allowed'   => $user
                    ? ($user->hasRole('super_admin') || $user->canEnterModule($module))
                    : true,
                'is_last'   => $user && $lastModule === $module,
            ];
        }

        return view('core::portal', [
            'cards'     => $cards,
            'user'      => $user,
            'lastModule' => $lastModule,
        ]);
    }
}
