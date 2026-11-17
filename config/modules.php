<?php

/*
|--------------------------------------------------------------------------
| 模块注册表（DDWL 三合一）
|--------------------------------------------------------------------------
| 单一应用、四模块（core/erp/pm/soho）。此文件是三处"模块事实"的唯一来源：
|   1) 路由前缀与路由名前缀   2) 表前缀   3) 权限名前缀 / 视图命名空间
| 任何代码不得硬编码模块字符串，一律读 config('modules.*')。
*/

return [

    // 默认模块（未归属用户/门户/CLI 的兜底）
    'default' => 'core',

    'modules' => [

        'core' => [
            'label'        => '核心管理',
            'route_prefix' => '',            // 无前缀：/、/login、/core/*
            'route_name'   => 'core.',
            'table_prefix' => '',            // Core 表无前缀
            'view'         => 'core',
            'permission'   => 'core.',
            'assets'       => 'assets/core',
            'enabled'      => true,
        ],

        'erp' => [
            'label'        => 'ERP 电子厂管理',
            'route_prefix' => 'erp',
            'route_name'   => 'erp.',
            'table_prefix' => 'erp_',
            'view'         => 'erp',
            'permission'   => 'erp.',
            'assets'       => 'assets/erp',
            'provider'     => App\Erp\Providers\ErpServiceProvider::class,
            'enabled'      => true,
        ],

        'pm' => [
            'label'        => '研发项目管理',
            'route_prefix' => 'pm',
            'route_name'   => 'pm.',
            'table_prefix' => 'pm_',
            'view'         => 'pm',
            'permission'   => 'pm.',
            'assets'       => 'assets/pm',
            'provider'     => App\Pm\Providers\PmServiceProvider::class,
            'enabled'      => true,
        ],

        'soho' => [
            'label'        => '多平台电商管理',
            'route_prefix' => 'soho',
            'route_name'   => 'soho.',
            'table_prefix' => 'soho_',
            'view'         => 'soho',
            'permission'   => 'soho.',
            'assets'       => 'assets/soho',
            'provider'     => App\Soho\Providers\SohoServiceProvider::class,
            'enabled'      => true,
        ],

    ],

    /*
    | 模块上下文的传递方式（彻底合并：单一 Model + 上下文作用域）
    | 见 A-规划方案书 §2.3 / P0 D20
    */
    'context' => [
        // 路由组中间件写 session，供子请求/队列任务读取
        'session_key' => 'active_module',
        // 允许请求头显式指定（API / 自动化测试用）
        'header'      => 'X-Module',
        // 队列任务必须显式携带（CLI 无 session）
        'queue_key'   => 'module',
    ],

    /*
    | 门户首页三张卡片的顺序与展示
    */
    'portal' => [
        'cards' => ['erp', 'pm', 'soho'],
    ],

];
