<?php

namespace App\Soho\Support;

/**
 * 菜单配置类 - V3.1 优化方案
 * 核心原则：
 * 1. 后台角色（super_admin/tenant_admin/security_admin/shop_auth_admin/integration_ops/auditor）
 *    只负责系统管理、安全审计、平台授权、集成运维
 *    禁止访问：商品、订单、仓储、物流、售后、财务、采购等前台业务菜单
 * 2. 前台角色负责业务操作
 *    禁止访问：用户管理、角色管理、系统参数、API日志、事件日志等后台菜单
 * 3. 仓库岗位（picker/checker/packer）使用任务型菜单，不使用管理型菜单
 * 4. 每个角色只能看到其职责范围内的菜单入口
 */
class MenuConfig
{
    /**
     * 获取所有菜单定义
     * 每个菜单绑定角色和权限
     */
    /**
     * 菜单路由名解析：参考项目菜单写的是短名（dashboard / order.index …），
     * 合并后真实路由统一带 soho. 前缀。这里只做运行时映射，参考菜单原文保持不变。
     */
    public static function getMenus(): array
    {
        $menus = self::getMenusRaw();
        $router = app()->bound('router') ? app('router')->getRoutes() : null;

        if (! $router) {
            return $menus;
        }

        foreach ($menus as $key => $menu) {
            $route = $menu['route'] ?? null;
            if (is_string($route) && $route !== '' && $route[0] !== '#' && ! str_contains($route, '://')) {
                if (! $router->getByName($route) && $router->getByName('soho.'.$route)) {
                    $menus[$key]['route'] = 'soho.'.$route;
                }
            }
        }

        return $menus;
    }

    public static function getMenusRaw(): array
    {
        // 所有角色列表（V3.1 - 26个角色）
        $allRoles = [
            'super_admin', 'tenant_admin', 'security_admin', 'shop_auth_admin',
            'operation_manager', 'platform_operator', 'product_admin', 'product_reviewer',
            'pricing_staff', 'order_manager', 'customer_service', 'warehouse_manager',
            'picker', 'checker', 'packer', 'logistics_specialist', 'aftersale_manager',
            'finance_staff', 'finance_manager', 'procurement_staff', 'data_analyst',
            'integration_ops', 'auditor', 'read_only', 'marketing_staff', 'procurement_manager',
        ];

        // 公共角色 - 所有角色都可以访问
        $allRolesViewable = $allRoles;

        return [
            // ========== 工作台（所有角色可见 - 统一首页） ==========
            'dashboard' => [
                'title' => '首页',
                'icon' => 'fa-home',
                'route' => 'dashboard',
                'permission' => 'dashboard.view',
                'roles' => $allRolesViewable,
                'order' => 1,
            ],

            // ========== 经营分析（报表 - 按需开放） ==========
            'sales_report' => [
                'title' => '销售报表',
                'icon' => 'fa-line-chart',
                'route' => 'sales.report',
                'permission' => 'report.sales',
                'roles' => ['super_admin', 'operation_manager', 'finance_manager', 
                           'pricing_staff', 'data_analyst', 'auditor', 'read_only', 'marketing_staff'],
                'order' => 10,
            ],
            'stock_report' => [
                'title' => '库存报表',
                'icon' => 'fa-file-text',
                'route' => 'report.inventory',
                'permission' => 'report.stock',
                'roles' => ['super_admin', 'data_analyst', 
                           'auditor', 'read_only'],
                'order' => 11,
            ],
            'product_report' => [
                'title' => '商品报表',
                'icon' => 'fa-bar-chart',
                'route' => 'product.ranking',
                'permission' => 'report.product',
                'roles' => ['super_admin', 'operation_manager', 'product_admin', 'product_reviewer', 
                           'platform_operator', 'pricing_staff', 'data_analyst', 'auditor', 
                           'read_only', 'marketing_staff'],
                'order' => 12,
            ],
            'profit_report' => [
                'title' => '利润报表',
                'icon' => 'fa-dollar',
                'route' => 'report.profit',
                'permission' => 'report.profit',
                'roles' => ['super_admin', 'operation_manager', 'finance_manager', 
                           'pricing_staff', 'data_analyst', 'auditor'],
                'order' => 13,
            ],
            'finance_report' => [
                'title' => '财务报表',
                'icon' => 'fa-money',
                'route' => 'report.profit',
                'permission' => 'report.profit',
                'roles' => ['super_admin', 'operation_manager', 'finance_manager', 'pricing_staff', 'data_analyst', 'auditor'],
                'order' => 14,
            ],

            // ========== 企业管理（仅后台角色） ==========
            'user_management' => [
                'title' => '用户管理',
                'icon' => 'fa-users',
                'route' => 'admin.users.index',
                'permission' => 'user.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 20,
            ],
            'department_management' => [
                'title' => '部门管理',
                'icon' => 'fa-building',
                'route' => 'admin.departments.index',
                'permission' => 'department.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 21,
            ],
            'role_management' => [
                'title' => '角色管理',
                'icon' => 'fa-key',
                'route' => 'admin.roles.index',
                'permission' => 'role.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 22,
            ],
            'data_scope_management' => [
                'title' => '数据权限',
                'icon' => 'fa-shield',
                'route' => 'data_scopes.index',
                'permission' => 'data_scope.manage',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 23,
            ],

            // ========== 系统管理（仅后台角色） ==========
            'system_settings' => [
                'title' => '系统设置',
                'icon' => 'fa-cog',
                'route' => 'system.settings',
                'permission' => 'system.view',
                'roles' => ['super_admin', 'tenant_admin', 'security_admin'],
                'order' => 30,
            ],
            'approval_flow_management' => [
                'title' => '审批流管理',
                'icon' => 'fa-check-square',
                'route' => 'approval_flows.index',
                'permission' => 'approval_flow.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 31,
            ],
            'approval_instance_management' => [
                'title' => '审批实例',
                'icon' => 'fa-hourglass-half',
                'route' => 'approval_instances.index',
                'permission' => 'approval.instance.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 32,
            ],
            'tax_rate_management' => [
                'title' => '税率管理',
                'icon' => 'fa-percent',
                'route' => 'tax-rate.index',
                'permission' => 'tax_rate.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 33,
            ],
            'currency_management' => [
                'title' => '货币管理',
                'icon' => 'fa-dollar',
                'route' => 'currencies.index',
                'permission' => 'currency.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 34,
            ],
            'export_task_management' => [
                'title' => '导出任务',
                'icon' => 'fa-download',
                'route' => 'export_tasks.index',
                'permission' => 'export_task.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 35,
            ],
            'system_parameters' => [
                'title' => '系统参数',
                'icon' => 'fa-sliders',
                'route' => 'system.parameters.index',
                'permission' => 'system.config',
                'roles' => ['super_admin'],
                'order' => 36,
            ],
            'feature_switch' => [
                'title' => '功能开关',
                'icon' => 'fa-toggle-on',
                'route' => 'system.features.index',
                'permission' => 'system.config',
                'roles' => ['super_admin'],
                'order' => 37,
            ],
            'notification_templates' => [
                'title' => '通知模板',
                'icon' => 'fa-bell',
                'route' => 'system.notifications.index',
                'permission' => 'system.config',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 38,
            ],
            'file_storage' => [
                'title' => '文件存储',
                'icon' => 'fa-database',
                'route' => 'system.storage.index',
                'permission' => 'system.config',
                'roles' => ['super_admin'],
                'order' => 39,
            ],
            'system_version' => [
                'title' => '系统版本',
                'icon' => 'fa-info-circle',
                'route' => 'system.version',
                'permission' => 'system.view',
                'roles' => ['super_admin', 'tenant_admin'],
                'order' => 40,
            ],

            // ========== 店铺与平台（店铺授权管理员 + 运营 + 平台运营） ==========
            'shop_management' => [
                'title' => '店铺管理',
                'icon' => 'fa-shopping-cart',
                'route' => 'shop.index',
                'permission' => 'shop.view',
                'roles' => ['super_admin', 'shop_auth_admin', 'operation_manager', 'platform_operator', 
                           'integration_ops', 'marketing_staff'],
                'order' => 40,
            ],
            'shop_authorize' => [
                'title' => '店铺授权',
                'icon' => 'fa-lock',
                'route' => 'shop.authorize',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 41,
            ],
            'platform_account_management' => [
                'title' => '平台账号',
                'icon' => 'fa-id-badge',
                'route' => 'platform_accounts.index',
                'permission' => 'platform_account.view',
                'roles' => ['shop_auth_admin', 'integration_ops'],
                'order' => 42,
            ],
            'platform_capability' => [
                'title' => '平台能力',
                'icon' => 'fa-cogs',
                'route' => 'platform_capabilities.index',
                'permission' => 'platform_account.view',
                'roles' => ['shop_auth_admin', 'integration_ops'],
                'order' => 43,
            ],

            // ========== 商品中心（商品管理员 + 审核员 + 运营 + 平台运营） ==========
            'product_spu' => [
                'title' => '商品 SPU',
                'icon' => 'fa-cube',
                'route' => 'product.index',
                'permission' => 'product.view',
                'roles' => ['super_admin', 'product_admin', 'product_reviewer', 'platform_operator', 
                           'operation_manager', 'pricing_staff', 'read_only'],
                'order' => 50,
            ],
            'goods_management' => [
                'title' => '商品管理',
                'icon' => 'fa-box',
                'route' => 'goods.index',
                'permission' => 'product.view',
                'roles' => ['super_admin', 'product_admin', 'platform_operator', 'operation_manager', 
                           'product_reviewer', 'pricing_staff', 'read_only'],
                'order' => 51,
            ],
            'sku_mapping' => [
                'title' => 'SKU 映射',
                'icon' => 'fa-exchange',
                'route' => 'platform-sku.index',
                'permission' => 'sku.view',
                'roles' => ['super_admin', 'product_admin', 'product_reviewer', 
                           'platform_operator', 'pricing_staff', 'operation_manager', 'read_only'],
                'order' => 52,
            ],
            'brand_management' => [
                'title' => '品牌管理',
                'icon' => 'fa-star',
                'route' => 'brand.index',
                'permission' => 'brand.view',
                'roles' => ['super_admin', 'product_admin', 'product_reviewer', 'operation_manager', 'platform_operator'],
                'order' => 53,
            ],
            'category_management' => [
                'title' => '分类管理',
                'icon' => 'fa-folder',
                'route' => 'category.index',
                'permission' => 'category.view',
                'roles' => ['super_admin', 'product_admin', 'product_reviewer', 'platform_operator', 
                           'operation_manager'],
                'order' => 54,
            ],
            'product_attribute' => [
                'title' => '商品属性',
                'icon' => 'fa-list',
                'route' => 'product-attribute.index',
                'permission' => 'product.attribute.view',
                'roles' => ['super_admin', 'product_admin', 'platform_operator', 'operation_manager'],
                'order' => 55,
            ],
            'media_library' => [
                'title' => '媒体库',
                'icon' => 'fa-picture',
                'route' => 'media.index',
                'permission' => 'media.view',
                'roles' => ['super_admin', 'product_admin', 'platform_operator', 'operation_manager', 
                           'product_reviewer'],
                'order' => 56,
            ],

            // ========== 价格中心（定价人员专用 + 供应链主管 + 超级管理员） ==========
            'price_query' => [
                'title' => '价格查询',
                'icon' => 'fa-search',
                'route' => 'price.query',
                'permission' => 'price.view',
                // 清理：移除 platform_operator（仅有未使用的 price.update）
                // 补充 super_admin（拥有全部权限，需显式列出才能通过 hasRole 检查）
                'roles' => ['super_admin', 'pricing_staff', 'procurement_manager', 'operation_manager'],
                'order' => 60,
            ],
            'price_rule' => [
                'title' => '价格规则',
                'icon' => 'fa-list-alt',
                'route' => 'price.rules.index',
                'permission' => 'price.rule.view',
                // 清理：仅 pricing_staff 拥有 price.rule.view 权限
                'roles' => ['super_admin', 'pricing_staff'],
                'order' => 61,
            ],
            'price_calculate' => [
                'title' => '价格计算',
                'icon' => 'fa-calculator',
                'route' => 'price.calculate',
                'permission' => 'price.view',
                // 清理：移除 platform_operator（无 price.view）
                'roles' => ['super_admin', 'pricing_staff', 'procurement_manager', 'operation_manager'],
                'order' => 62,
            ],
            'price_change_request' => [
                'title' => '调价申请',
                'icon' => 'fa-edit',
                'route' => 'price.change.requests.index',
                'permission' => 'price.change.request',
                // 清理：仅拥有 price.change.request 权限的角色（pricing_staff + platform_operator）
                'roles' => ['super_admin', 'pricing_staff', 'platform_operator'],
                'order' => 63,
            ],
            'price_change_task' => [
                'title' => '调价任务',
                'icon' => 'fa-tasks',
                'route' => 'price.change.tasks.index',
                'permission' => 'price.change.execute',
                // 清理：仅 pricing_staff 拥有 price.change.execute 权限
                'roles' => ['super_admin', 'pricing_staff'],
                'order' => 64,
            ],
            'price_change_history' => [
                'title' => '调价历史',
                'icon' => 'fa-history',
                'route' => 'price.change.history',
                'permission' => 'price.view',
                // 清理：移除 platform_operator（无 price.view）
                'roles' => ['super_admin', 'pricing_staff', 'procurement_manager', 'operation_manager'],
                'order' => 65,
            ],

            // ========== 订单中心（订单主管 + 客服 + 财务 + 售后 + 审计） ==========
            'order_all' => [
                'title' => '全部订单',
                'icon' => 'fa-shopping-bag',
                'route' => 'order.index',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 70,
            ],
            'order_pending_payment' => [
                'title' => '待付款',
                'icon' => 'fa-money',
                'route' => 'order.pending-payment',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 71,
            ],
            'order_pending_ship' => [
                'title' => '待发货',
                'icon' => 'fa-clock',
                'route' => 'order.pending-ship',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 72,
            ],
            'order_shipped' => [
                'title' => '已发货',
                'icon' => 'fa-truck',
                'route' => 'order.shipped',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 73,
            ],
            'order_completed' => [
                'title' => '已完成',
                'icon' => 'fa-check-circle',
                'route' => 'order.completed',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 74,
            ],
            'order_cancelled' => [
                'title' => '已取消',
                'icon' => 'fa-ban',
                'route' => 'order.cancelled',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 75,
            ],
            'order_exception' => [
                'title' => '异常订单',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'order.exception',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'order_manager', 'customer_service', 'operation_manager', 'read_only'],
                'order' => 76,
            ],

            // ========== 客户中心（客服 + 运营 + 平台运营） ==========
            'customer_management' => [
                'title' => '客户管理',
                'icon' => 'fa-user',
                'route' => 'customer.index',
                'permission' => 'customer.view',
                'roles' => ['super_admin', 'customer_service', 'operation_manager'],
                'order' => 80,
            ],
            'customer_groups' => [
                'title' => '客户分组',
                'icon' => 'fa-users',
                'route' => 'customer.groups',
                'permission' => 'customer.view',
                'roles' => ['super_admin', 'customer_service', 'operation_manager'],
                'order' => 81,
            ],

            // ========== 营销中心（营销专员 + 运营 + 平台运营） ==========
            'promotion_management' => [
                'title' => '促销活动',
                'icon' => 'fa-bullhorn',
                'route' => 'promotion.index',
                'permission' => 'promotion.view',
                'roles' => ['super_admin', 'marketing_staff', 'operation_manager', 'platform_operator'],
                'order' => 90,
            ],
            'coupon_management' => [
                'title' => '优惠券管理',
                'icon' => 'fa-ticket',
                'route' => 'coupon.index',
                'permission' => 'coupon.view',
                'roles' => ['super_admin', 'marketing_staff', 'operation_manager', 'platform_operator'],
                'order' => 91,
            ],
            'promotion_internal' => [
                'title' => '内部促销',
                'icon' => 'fa-tag',
                'route' => 'promotion.internal',
                'permission' => 'promotion.view',
                'roles' => ['marketing_staff'],
                'order' => 92,
            ],
            'promotion_analysis' => [
                'title' => '促销分析',
                'icon' => 'fa-bar-chart',
                'route' => 'promotion.analysis',
                'permission' => 'promotion.view',
                'roles' => ['marketing_staff'],
                'order' => 93,
            ],

            // ========== 仓储中心（仓库主管管理） ==========
            'warehouse_management' => [
                'title' => '仓库管理',
                'icon' => 'fa-database',
                'route' => 'warehouse.index',
                'permission' => 'warehouse.view',
                'roles' => ['super_admin', 'warehouse_manager', 'operation_manager', 'order_manager', 
                           'data_analyst', 'read_only'],
                'order' => 100,
            ],
            'inventory_management' => [
                'title' => '库存查询',
                'icon' => 'fa-cubes',
                'route' => 'inventory.index',
                'permission' => 'inventory.view',
                'roles' => ['super_admin', 'warehouse_manager', 'operation_manager', 
                           'order_manager', 'data_analyst', 'read_only'],
                'order' => 101,
            ],
            'inventory_flow' => [
                'title' => '库存流水',
                'icon' => 'fa-exchange',
                'route' => 'inventory.flow',
                'permission' => 'inventory.view',
                'roles' => ['warehouse_manager'],
                'order' => 102,
            ],
            'inventory_in' => [
                'title' => '入库管理',
                'icon' => 'fa-arrow-down',
                'route' => 'inventory.in',
                'permission' => 'inventory.adjust',
                'roles' => ['warehouse_manager'],
                'order' => 103,
            ],
            'inventory_out' => [
                'title' => '出库管理',
                'icon' => 'fa-arrow-up',
                'route' => 'inventory.out',
                'permission' => 'inventory.adjust',
                'roles' => ['warehouse_manager'],
                'order' => 104,
            ],
            'inventory_transfer' => [
                'title' => '库存调拨',
                'icon' => 'fa-random',
                'route' => 'inventory.transfer',
                'permission' => 'inventory.transfer',
                'roles' => ['warehouse_manager'],
                'order' => 105,
            ],
            'inventory_stocktake' => [
                'title' => '库存盘点',
                'icon' => 'fa-clipboard',
                'route' => 'inventory.stocktake',
                'permission' => 'inventory.stocktake',
                'roles' => ['warehouse_manager'],
                'order' => 106,
            ],
            'inventory_batch_management' => [
                'title' => '库存批次',
                'icon' => 'fa-layer-group',
                'route' => 'inventory.batches',
                'permission' => 'inventory.batch.view',
                'roles' => ['warehouse_manager', 'procurement_staff'],
                'order' => 107,
            ],

            // ========== 履约作业（仓库岗位专用 - 任务型菜单） ==========
            // 拣货员任务
            'pick_task_pending' => [
                'title' => '待拣货任务',
                'icon' => 'fa-list',
                'route' => 'pick.tasks.index',
                'permission' => 'pick.task.view',
                'roles' => ['picker'],
                'order' => 110,
            ],
            'pick_task_scan' => [
                'title' => '扫码拣货',
                'icon' => 'fa-qrcode',
                'route' => 'pick.tasks.scan',
                'permission' => 'pick.task.view',
                'roles' => ['picker'],
                'order' => 111,
            ],
            'pick_task_exception' => [
                'title' => '拣货异常',
                'icon' => 'fa-exclamation-circle',
                'route' => 'pick.tasks.exception',
                'permission' => 'pick.task.view',
                'roles' => ['picker'],
                'order' => 112,
            ],
            // 复核员任务
            'check_task_pending' => [
                'title' => '待复核任务',
                'icon' => 'fa-list',
                'route' => 'check.tasks.index',
                'permission' => 'check.task.view',
                'roles' => ['checker'],
                'order' => 113,
            ],
            'check_task_scan' => [
                'title' => '扫码复核',
                'icon' => 'fa-qrcode',
                'route' => 'check.tasks.scan',
                'permission' => 'check.task.view',
                'roles' => ['checker'],
                'order' => 114,
            ],
            // 打包员任务
            'pack_task_pending' => [
                'title' => '待打包任务',
                'icon' => 'fa-list',
                'route' => 'pack.tasks.index',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 115,
            ],
            'pack_task_scan' => [
                'title' => '扫码打包',
                'icon' => 'fa-qrcode',
                'route' => 'pack.tasks.scan',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 116,
            ],
            // 作业协同（主管查看）
            'shipment_pending' => [
                'title' => '待发货包裹',
                'icon' => 'fa-box',
                'route' => 'shipment.pending',
                'permission' => 'shipment.view',
                'roles' => ['order_manager', 'warehouse_manager'],
                'order' => 117,
            ],
            'fulfillment_exception' => [
                'title' => '作业异常',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'fulfillment.exception',
                'permission' => 'fulfillment.view',
                'roles' => ['order_manager', 'warehouse_manager'],
                'order' => 118,
            ],

            // ========== 物流中心（物流专员主管） ==========
            'package_management' => [
                'title' => '包裹管理',
                'icon' => 'fa-cubes',
                'route' => 'package.index',
                'permission' => 'package.view',
                'roles' => ['logistics_specialist', 'warehouse_manager', 'order_manager', 'packer'],
                'order' => 120,
            ],
            'express_management' => [
                'title' => '快递公司',
                'icon' => 'fa-truck',
                'route' => 'express.index',
                'permission' => 'express.view',
                'roles' => ['logistics_specialist', 'warehouse_manager', 'order_manager', 'packer'],
                'order' => 121,
            ],
            'shipping_method' => [
                'title' => '配送方式',
                'icon' => 'fa-truck',
                'route' => 'shipping-method.index',
                'permission' => 'shipping.view',
                'roles' => ['logistics_specialist', 'warehouse_manager', 'packer'],
                'order' => 122,
            ],
            'shipping_template' => [
                'title' => '运费模板',
                'icon' => 'fa-table',
                'route' => 'shipping-templates.index',
                'permission' => 'shipping.view',
                'roles' => ['logistics_specialist', 'warehouse_manager', 'packer'],
                'order' => 123,
            ],
            'waybill_management' => [
                'title' => '面单管理',
                'icon' => 'fa-file',
                'route' => 'waybill.index',
                'permission' => 'logistics.view',
                'roles' => ['logistics_specialist'],
                'order' => 124,
            ],
            'logistics_tracking' => [
                'title' => '物流轨迹',
                'icon' => 'fa-map-marker',
                'route' => 'logistics.tracking',
                'permission' => 'logistics.view',
                'roles' => ['logistics_specialist'],
                'order' => 125,
            ],
            'logistics_exception' => [
                'title' => '异常物流',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'logistics.exception',
                'permission' => 'logistics.view',
                'roles' => ['logistics_specialist'],
                'order' => 126,
            ],

            // ========== 售后中心（客服申请 + 售后主管审核） ==========
            'aftersale_management' => [
                'title' => '售后管理',
                'icon' => 'fa-life-ring',
                'route' => 'aftersale.index',
                'permission' => 'after_sale.view',
                'roles' => ['super_admin', 'customer_service', 'aftersale_manager'],
                'order' => 130,
            ],
            'aftersale_pending' => [
                'title' => '待处理售后',
                'icon' => 'fa-hourglass',
                'route' => 'aftersale.pending',
                'permission' => 'after_sale.approve',
                'roles' => ['super_admin', 'customer_service', 'aftersale_manager'],
                'order' => 131,
            ],
            'refund_records' => [
                'title' => '退款记录',
                'icon' => 'fa-undo',
                'route' => 'aftersale.refunds',
                'permission' => 'after_sale.refund',
                'roles' => ['super_admin', 'customer_service', 'aftersale_manager'],
                'order' => 132,
            ],
            'refund_approve' => [
                'title' => '退款审批',
                'icon' => 'fa-check',
                'route' => 'refund.approve',
                'permission' => 'refund.approve',
                'roles' => ['aftersale_manager'],
                'order' => 133,
            ],
            'return_receive' => [
                'title' => '退货收货',
                'icon' => 'fa-inbox',
                'route' => 'return.receive',
                'permission' => 'return.receive',
                'roles' => ['aftersale_manager'],
                'order' => 134,
            ],
            'return_inspect' => [
                'title' => '退货质检',
                'icon' => 'fa-search',
                'route' => 'return.inspect',
                'permission' => 'return.inspect',
                'roles' => ['aftersale_manager'],
                'order' => 135,
            ],

            // ========== 财务中心（财务人员 + 财务主管） ==========
            'finance_receive' => [
                'title' => '收款记录',
                'icon' => 'fa-money',
                'route' => 'finance.receive',
                'permission' => 'finance.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 140,
            ],
            'finance_reconcile' => [
                'title' => '对账管理',
                'icon' => 'fa-balance-scale',
                'route' => 'finance.reconcile',
                'permission' => 'finance.reconcile',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 141,
            ],
            'finance_settle' => [
                'title' => '结算管理',
                'icon' => 'fa-calculator',
                'route' => 'finance.settle',
                'permission' => 'finance.settle',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 142,
            ],
            'finance_transaction' => [
                'title' => '财务流水',
                'icon' => 'fa-exchange',
                'route' => 'finance.transactions',
                'permission' => 'finance.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 143,
            ],
            'tax_entity_management' => [
                'title' => '税务主体',
                'icon' => 'fa-file-alt',
                'route' => 'tax_entities.index',
                'permission' => 'tax_entity.view',
                'roles' => ['finance_manager'],
                'order' => 144,
            ],
            'exchange_rate_management' => [
                'title' => '汇率管理',
                'icon' => 'fa-exchange-alt',
                'route' => 'exchange_rates.index',
                'permission' => 'exchange_rate.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 145,
            ],

            // ========== 采购中心（采购员 + 采购主管） ==========
            'procurement_plan' => [
                'title' => '采购计划',
                'icon' => 'fa-calendar',
                'route' => 'procurement.plans.index',
                'permission' => 'procurement.plan.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 150,
            ],
            'procurement_request' => [
                'title' => '采购申请',
                'icon' => 'fa-file',
                'route' => 'procurement.requests.index',
                'permission' => 'procurement.request.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 151,
            ],
            'procurement_order' => [
                'title' => '采购订单',
                'icon' => 'fa-file-text',
                'route' => 'procurement.orders.index',
                'permission' => 'procurement.order.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 152,
            ],
            'supplier_management' => [
                'title' => '供应商管理',
                'icon' => 'fa-users',
                'route' => 'procurement.suppliers.index',
                'permission' => 'supplier.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 153,
            ],
            'procurement_arrival' => [
                'title' => '采购到货',
                'icon' => 'fa-download',
                'route' => 'procurement.arrivals.index',
                'permission' => 'procurement.arrival',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 154,
            ],
            'procurement_inbound' => [
                'title' => '采购入库协同',
                'icon' => 'fa-warehouse',
                'route' => 'procurement.inbounds.index',
                'permission' => 'procurement.inbound',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 155,
            ],
            'procurement_report' => [
                'title' => '采购报表',
                'icon' => 'fa-bar-chart',
                'route' => 'report.procurement',
                'permission' => 'report.procurement',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 156,
            ],
            'safe_stock' => [
                'title' => '安全库存',
                'icon' => 'fa-shield',
                'route' => 'procurement.safe-stocks.index',
                'permission' => 'inventory.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 157,
            ],

            // ========== 系统运维（后台角色专用） ==========
            'sync_task' => [
                'title' => '同步任务',
                'icon' => 'fa-tasks',
                'route' => 'sync-task.index',
                'permission' => 'sync_task.view',
                'roles' => ['integration_ops', 'shop_auth_admin'],
                'order' => 160,
            ],
            'sync_log' => [
                'title' => '同步日志',
                'icon' => 'fa-file',
                'route' => 'log.sync-task.index',
                'permission' => 'log.view',
                'roles' => ['integration_ops', 'shop_auth_admin'],
                'order' => 161,
            ],
            'sync_cursor_management' => [
                'title' => '同步游标',
                'icon' => 'fa-sync',
                'route' => 'sync_cursors.index',
                'permission' => 'sync_cursor.view',
                'roles' => ['integration_ops'],
                'order' => 162,
            ],
            'webhook_log' => [
                'title' => 'Webhook日志',
                'icon' => 'fa-plug',
                'route' => 'log.webhook',
                'permission' => 'log.api',
                'roles' => ['integration_ops', 'super_admin'],
                'order' => 163,
            ],
            'api_log' => [
                'title' => 'API 日志',
                'icon' => 'fa-code',
                'route' => 'log.platform-api.index',
                'permission' => 'log.api',
                'roles' => ['super_admin', 'security_admin', 'integration_ops'],
                'order' => 164,
            ],
            'event_log' => [
                'title' => '事件日志',
                'icon' => 'fa-random',
                'route' => 'event-log.index',
                'permission' => 'event.view',
                'roles' => ['super_admin', 'integration_ops'],
                'order' => 165,
            ],
            'exception_management' => [
                'title' => '异常中心',
                'icon' => 'fa-exclamation-circle',
                'route' => 'exceptions.index',
                'permission' => 'exception.view',
                'roles' => ['super_admin', 'integration_ops'],
                'order' => 166,
            ],
            'event_replay' => [
                'title' => '事件重放',
                'icon' => 'fa-redo',
                'route' => 'event.replay',
                'permission' => 'event.replay',
                'roles' => ['integration_ops', 'super_admin'],
                'order' => 167,
            ],
            'queue_monitor' => [
                'title' => '队列监控',
                'icon' => 'fa-list-alt',
                'route' => 'queue.monitor',
                'permission' => 'system.view',
                'roles' => ['super_admin', 'integration_ops'],
                'order' => 168,
            ],
            'system_health' => [
                'title' => '系统健康检查',
                'icon' => 'fa-heartbeat',
                'route' => 'system.health',
                'permission' => 'system.view',
                'roles' => ['super_admin', 'integration_ops'],
                'order' => 169,
            ],
            'system_alerts' => [
                'title' => '系统告警',
                'icon' => 'fa-bell',
                'route' => 'system.alerts.index',
                'permission' => 'system.view',
                'roles' => ['super_admin', 'integration_ops'],
                'order' => 170,
            ],

            // ========== 安全审计（安全管理员 + 审计员） ==========
            'operation_log' => [
                'title' => '操作日志',
                'icon' => 'fa-file-text-o',
                'route' => 'operation-logs.index',
                'permission' => 'log.view',
                'roles' => ['security_admin', 'tenant_admin', 'auditor'],
                'order' => 170,
            ],
            'login_log' => [
                'title' => '登录日志',
                'icon' => 'fa-sign-in',
                'route' => 'log.login',
                'permission' => 'log.audit',
                'roles' => ['security_admin', 'auditor'],
                'order' => 171,
            ],
            'permission_change_log' => [
                'title' => '权限变更日志',
                'icon' => 'fa-key',
                'route' => 'log.permission-change',
                'permission' => 'log.audit',
                'roles' => ['security_admin', 'auditor'],
                'order' => 172,
            ],
            'sensitive_access_log' => [
                'title' => '敏感数据访问日志',
                'icon' => 'fa-eye-slash',
                'route' => 'log.sensitive-access',
                'permission' => 'log.audit',
                'roles' => ['security_admin', 'auditor'],
                'order' => 173,
            ],
            'security_event' => [
                'title' => '安全事件',
                'icon' => 'fa-shield',
                'route' => 'security.event',
                'permission' => 'security.view',
                'roles' => ['security_admin', 'auditor', 'super_admin'],
                'order' => 174,
            ],

            // ========== 安全中心（仅安全管理员 - V3.1遗漏补全） ==========
            'login_failures' => [
                'title' => '登录失败记录',
                'icon' => 'fa-ban',
                'route' => 'security.login.failures',
                'permission' => 'security.view',
                'roles' => ['security_admin'],
                'order' => 175,
            ],
            'session_management' => [
                'title' => '会话管理',
                'icon' => 'fa-user-circle',
                'route' => 'security.sessions',
                'permission' => 'security.view',
                'roles' => ['security_admin'],
                'order' => 176,
            ],
            'device_management' => [
                'title' => '设备管理',
                'icon' => 'fa-laptop',
                'route' => 'security.devices',
                'permission' => 'security.view',
                'roles' => ['security_admin'],
                'order' => 177,
            ],
            'ip_whitelist' => [
                'title' => 'IP 白名单',
                'icon' => 'fa-map-marker',
                'route' => 'security.ip-whitelist.index',
                'permission' => 'security.ip_whitelist',
                'roles' => ['security_admin'],
                'order' => 178,
            ],
            'mfa_policy' => [
                'title' => '二次验证策略',
                'icon' => 'fa-key',
                'route' => 'security.mfa',
                'permission' => 'security.config',
                'roles' => ['security_admin'],
                'order' => 179,
            ],
            'password_policy' => [
                'title' => '密码策略',
                'icon' => 'fa-lock',
                'route' => 'security.password-policy',
                'permission' => 'security.config',
                'roles' => ['security_admin'],
                'order' => 180,
            ],
            'account_lockout' => [
                'title' => '账号锁定记录',
                'icon' => 'fa-user-times',
                'route' => 'security.lockouts',
                'permission' => 'security.view',
                'roles' => ['security_admin'],
                'order' => 181,
            ],
            'security_audit_report' => [
                'title' => '审计报告',
                'icon' => 'fa-file-pdf-o',
                'route' => 'audit.reports',
                'permission' => 'log.audit',
                'roles' => ['security_admin', 'auditor', 'super_admin'],
                'order' => 182,
            ],

            // ========== 授权运维（店铺授权管理员 - V3.1遗漏补全） ==========
            'auth_status' => [
                'title' => '授权状态',
                'icon' => 'fa-check-circle',
                'route' => 'shop.authorization-status.index',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 183,
            ],
            'token_status' => [
                'title' => 'Token 状态',
                'icon' => 'fa-key',
                'route' => 'shop.token-status',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 184,
            ],
            'auth_operation_log' => [
                'title' => '授权操作日志',
                'icon' => 'fa-history',
                'route' => 'shop.authorization-logs.index',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 185,
            ],
            'auth_failure_log' => [
                'title' => '授权失败记录',
                'icon' => 'fa-times',
                'route' => 'shop.authorization-failures.index',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 186,
            ],
            'token_refresh_log' => [
                'title' => 'Token 刷新记录',
                'icon' => 'fa-refresh',
                'route' => 'shop.token-refresh-logs.index',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 187,
            ],
            'auth_exception' => [
                'title' => '授权异常',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'shop.authorization-exceptions.index',
                'permission' => 'shop.authorize',
                'roles' => ['shop_auth_admin'],
                'order' => 188,
            ],

            // ========== 集成运维扩展（V3.1遗漏补全） ==========
            'shop_connection_status' => [
                'title' => '店铺连接状态',
                'icon' => 'fa-plug',
                'route' => 'integration.connection.status',
                'permission' => 'sync_task.view',
                'roles' => ['integration_ops'],
                'order' => 189,
            ],
            'api_health_check' => [
                'title' => 'API 健康检查',
                'icon' => 'fa-heartbeat',
                'route' => 'integration.api.health',
                'permission' => 'system.view',
                'roles' => ['integration_ops'],
                'order' => 190,
            ],
            'sync_failed_tasks' => [
                'title' => '失败任务',
                'icon' => 'fa-times-circle',
                'route' => 'sync-task.failed',
                'permission' => 'sync_task.execute',
                'roles' => ['integration_ops'],
                'order' => 191,
            ],
            'sync_task_retry' => [
                'title' => '任务重试',
                'icon' => 'fa-repeat',
                'route' => 'sync-task.retry',
                'permission' => 'sync_task.execute',
                'roles' => ['integration_ops'],
                'order' => 192,
            ],
            'webhook_failures' => [
                'title' => 'Webhook 失败记录',
                'icon' => 'fa-exclamation-circle',
                'route' => 'log.webhook.failures',
                'permission' => 'log.api',
                'roles' => ['integration_ops', 'super_admin'],
                'order' => 193,
            ],
            'rate_limit_monitor' => [
                'title' => '限流监控',
                'icon' => 'fa-tachometer',
                'route' => 'integration.rate-limit',
                'permission' => 'system.view',
                'roles' => ['integration_ops', 'super_admin'],
                'order' => 194,
            ],

            // ========== 审计中心扩展（V3.1遗漏补全 + super_admin 修复） ==========
            'audit_export_log' => [
                'title' => '导出日志',
                'icon' => 'fa-file-excel-o',
                'route' => 'audit.export-log',
                'permission' => 'log.audit',
                'roles' => ['super_admin', 'security_admin', 'auditor'],
                'order' => 195,
            ],
            'approval_flow_record' => [
                'title' => '审批流记录',
                'icon' => 'fa-check-square-o',
                'route' => 'audit.approval-flow',
                'permission' => 'approval_flow.view',
                'roles' => ['super_admin', 'auditor'],
                'order' => 196,
            ],
            'product_change_log' => [
                'title' => '商品变更记录',
                'icon' => 'fa-history',
                'route' => 'audit.product-change',
                'permission' => 'product.view',
                'roles' => ['super_admin', 'auditor', 'operation_manager'],
                'order' => 197,
            ],
            'order_status_log' => [
                'title' => '订单状态记录',
                'icon' => 'fa-history',
                'route' => 'audit.order-status',
                'permission' => 'order.view',
                'roles' => ['super_admin', 'auditor', 'operation_manager'],
                'order' => 198,
            ],
            'reconciliation_diff_log' => [
                'title' => '对账差异记录',
                'icon' => 'fa-columns',
                'route' => 'audit.reconciliation-diff',
                'permission' => 'finance.reconcile',
                'roles' => ['super_admin', 'auditor', 'operation_manager'],
                'order' => 199,
            ],

            // ========== 运营主管扩展（V3.1补全） ==========
            'sales_report_dashboard' => [
                'title' => '经营看板',
                'icon' => 'fa-dashboard',
                'route' => 'operation.dashboard',
                'permission' => 'report.sales',
                'roles' => ['operation_manager'],
                'order' => 200,
            ],
            'aftersale_report' => [
                'title' => '售后报表',
                'icon' => 'fa-life-ring',
                'route' => 'report.aftersale',
                'permission' => 'report.aftersale',
                'roles' => ['operation_manager', 'data_analyst'],
                'order' => 201,
            ],
            'product_publish_approval' => [
                'title' => '商品发布审批',
                'icon' => 'fa-check',
                'route' => 'approval.product-publish.index',
                'permission' => 'product.approve',
                'roles' => ['operation_manager', 'product_admin', 'product_reviewer', 'super_admin'],
                'order' => 202,
            ],
            'price_change_approval' => [
                'title' => '调价审批',
                'icon' => 'fa-dollar',
                'route' => 'price.change.approvals.index',
                'permission' => 'price.change.approve',
                // 清理：移除 pricing_staff（无 price.change.approve）、platform_operator（无 price.change.approve）
                // 补充 super_admin（拥有全部权限）
                'roles' => ['super_admin', 'operation_manager', 'procurement_manager'],
                'order' => 203,
            ],
            'order_cancel_approval' => [
                'title' => '订单取消审批',
                'icon' => 'fa-ban',
                'route' => 'approval.order-cancel.index',
                'permission' => 'order.approve',
                'roles' => ['operation_manager', 'order_manager', 'super_admin'],
                'order' => 204,
            ],
            'inventory_adjust_approval' => [
                'title' => '库存调整审批',
                'icon' => 'fa-exchange',
                'route' => 'approval.inventory-adjust.index',
                'permission' => 'inventory.approve',
                'roles' => ['operation_manager', 'warehouse_manager', 'super_admin'],
                'order' => 205,
            ],
            'refund_approval_page' => [
                'title' => '售后退款审批',
                'icon' => 'fa-undo',
                'route' => 'approval.refund.index',
                'permission' => 'refund.approve',
                'roles' => ['operation_manager', 'aftersale_manager', 'super_admin'],
                'order' => 206,
            ],
            'shop_overview' => [
                'title' => '店铺经营概览',
                'icon' => 'fa-store',
                'route' => 'operation.shop.overview',
                'permission' => 'shop.view',
                'roles' => ['operation_manager'],
                'order' => 207,
            ],
            'inventory_warning' => [
                'title' => '库存预警',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'operation.inventory.warning',
                'permission' => 'inventory.view',
                'roles' => ['operation_manager'],
                'order' => 208,
            ],
            'ship_on_time_rate' => [
                'title' => '发货及时率',
                'icon' => 'fa-clock',
                'route' => 'operation.ship.on-time',
                'permission' => 'order.view',
                'roles' => ['operation_manager'],
                'order' => 209,
            ],
            'aftersale_rate_analysis' => [
                'title' => '售后率分析',
                'icon' => 'fa-chart-line',
                'route' => 'operation.aftersale.rate',
                'permission' => 'report.aftersale',
                'roles' => ['operation_manager'],
                'order' => 210,
            ],
            'exception_order_overview' => [
                'title' => '异常订单概览',
                'icon' => 'fa-exclamation-circle',
                'route' => 'operation.order.exception',
                'permission' => 'order.view',
                'roles' => ['operation_manager'],
                'order' => 211,
            ],

            // ========== 平台运营扩展（V3.1补全） ==========
            'listing_management' => [
                'title' => 'Listing 管理',
                'icon' => 'fa-list',
                'route' => 'platform.listing',
                'permission' => 'product.view',
                'roles' => ['platform_operator'],
                'order' => 212,
            ],
            'platform_product_publish' => [
                'title' => '平台商品发布',
                'icon' => 'fa-upload',
                'route' => 'platform.product.publish',
                'permission' => 'product.create',
                'roles' => ['platform_operator'],
                'order' => 213,
            ],
            'publish_task' => [
                'title' => '商品发布任务',
                'icon' => 'fa-tasks',
                'route' => 'platform.publish.task',
                'permission' => 'product.view',
                'roles' => ['platform_operator'],
                'order' => 214,
            ],
            'platform_category_mapping' => [
                'title' => '分类映射',
                'icon' => 'fa-map',
                'route' => 'platform.category.mapping',
                'permission' => 'category.view',
                'roles' => ['platform_operator'],
                'order' => 215,
            ],
            'platform_attribute' => [
                'title' => '平台属性',
                'icon' => 'fa-sliders',
                'route' => 'platform.attribute',
                'permission' => 'product.attribute.view',
                'roles' => ['platform_operator'],
                'order' => 216,
            ],

            // ========== 商品管理员扩展（V3.1补全） ==========
            'sku_management' => [
                'title' => 'SKU 管理',
                'icon' => 'fa-cubes',
                'route' => 'product.sku',
                'permission' => 'sku.view',
                'roles' => ['product_admin', 'product_reviewer'],
                'order' => 217,
            ],
            'product_version' => [
                'title' => '商品版本',
                'icon' => 'fa-code-fork',
                'route' => 'product.versions',
                'permission' => 'product.view',
                'roles' => ['product_admin', 'product_reviewer'],
                'order' => 218,
            ],
            'product_import' => [
                'title' => '商品导入',
                'icon' => 'fa-upload',
                'route' => 'product.import',
                'permission' => 'product.create',
                'roles' => ['product_admin'],
                'order' => 219,
            ],
            'product_task_pending_edit' => [
                'title' => '待修改商品',
                'icon' => 'fa-pencil',
                'route' => 'product.tasks.pending-edit',
                'permission' => 'product.view',
                'roles' => ['product_admin'],
                'order' => 220,
            ],
            'product_task_returned' => [
                'title' => '退回商品',
                'icon' => 'fa-undo',
                'route' => 'product.tasks.returned',
                'permission' => 'product.view',
                'roles' => ['product_admin'],
                'order' => 221,
            ],
            'product_task_exception' => [
                'title' => '商品资料异常',
                'icon' => 'fa-exclamation',
                'route' => 'product.tasks.exception',
                'permission' => 'product.view',
                'roles' => ['product_admin'],
                'order' => 222,
            ],

            // ========== 商品审核员扩展（V3.1补全） ==========
            'product_review_pending' => [
                'title' => '待审核商品',
                'icon' => 'fa-hourglass',
                'route' => 'product.review.pending',
                'permission' => 'product.review',
                'roles' => ['product_reviewer'],
                'order' => 223,
            ],
            'product_review_record' => [
                'title' => '商品审核记录',
                'icon' => 'fa-history',
                'route' => 'product.review.record',
                'permission' => 'product.view',
                'roles' => ['product_reviewer'],
                'order' => 224,
            ],
            'product_change_review' => [
                'title' => '商品变更审核',
                'icon' => 'fa-edit',
                'route' => 'product.review.change',
                'permission' => 'product.review',
                'roles' => ['product_reviewer'],
                'order' => 225,
            ],
            'product_spu_detail' => [
                'title' => 'SPU 详情',
                'icon' => 'fa-info-circle',
                'route' => 'product.spu.detail',
                'permission' => 'product.view',
                'roles' => ['product_reviewer'],
                'order' => 226,
            ],

            // ========== 订单主管扩展（V3.1补全） ==========
            'order_pending_fulfill' => [
                'title' => '待履约',
                'icon' => 'fa-hourglass-half',
                'route' => 'order.pending-fulfill',
                'permission' => 'order.view',
                'roles' => ['order_manager'],
                'order' => 227,
            ],
            'order_split' => [
                'title' => '订单拆分',
                'icon' => 'fa-code-fork',
                'route' => 'order.split',
                'permission' => 'order.edit',
                'roles' => ['order_manager'],
                'order' => 228,
            ],
            'order_merge' => [
                'title' => '订单合并',
                'icon' => 'fa-code-merge',
                'route' => 'order.merge',
                'permission' => 'order.edit',
                'roles' => ['order_manager'],
                'order' => 229,
            ],
            'order_export' => [
                'title' => '订单导出',
                'icon' => 'fa-download',
                'route' => 'order.export',
                'permission' => 'order.view',
                'roles' => ['order_manager'],
                'order' => 230,
            ],
            'warehouse_assignment' => [
                'title' => '分仓管理',
                'icon' => 'fa-building',
                'route' => 'order.warehouse.assign',
                'permission' => 'order.edit',
                'roles' => ['order_manager'],
                'order' => 231,
            ],
            'fulfillment_order' => [
                'title' => '履约单',
                'icon' => 'fa-file-text',
                'route' => 'order.fulfillment',
                'permission' => 'order.view',
                'roles' => ['order_manager'],
                'order' => 232,
            ],
            'order_stock_shortage' => [
                'title' => '缺货订单',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'order.stock-shortage',
                'permission' => 'order.view',
                'roles' => ['order_manager'],
                'order' => 233,
            ],

            // ========== 客服扩展（V3.1补全） ==========
            'customer_tag' => [
                'title' => '客户标签',
                'icon' => 'fa-tags',
                'route' => 'customer.tags',
                'permission' => 'customer.view',
                'roles' => ['customer_service'],
                'order' => 234,
            ],
            'customer_privacy_request' => [
                'title' => '客户隐私请求',
                'icon' => 'fa-user-secret',
                'route' => 'customer.privacy',
                'permission' => 'customer.view',
                'roles' => ['customer_service'],
                'order' => 235,
            ],
            'aftersale_apply' => [
                'title' => '售后申请',
                'icon' => 'fa-life-ring',
                'route' => 'aftersale.apply',
                'permission' => 'after_sale.view',
                'roles' => ['customer_service'],
                'order' => 236,
            ],
            'aftersale_supplement' => [
                'title' => '待补充资料',
                'icon' => 'fa-paperclip',
                'route' => 'aftersale.supplement',
                'permission' => 'after_sale.view',
                'roles' => ['customer_service'],
                'order' => 237,
            ],
            'aftersale_progress' => [
                'title' => '售后进度',
                'icon' => 'fa-tasks',
                'route' => 'aftersale.progress',
                'permission' => 'after_sale.view',
                'roles' => ['customer_service'],
                'order' => 238,
            ],
            'aftersale_communication' => [
                'title' => '售后沟通记录',
                'icon' => 'fa-comments',
                'route' => 'aftersale.communication',
                'permission' => 'after_sale.view',
                'roles' => ['customer_service'],
                'order' => 239,
            ],

            // ========== 仓库主管扩展（V3.1补全） ==========
            'warehouse_zone' => [
                'title' => '库区管理',
                'icon' => 'fa-th-large',
                'route' => 'warehouse.zones',
                'permission' => 'warehouse.view',
                'roles' => ['warehouse_manager'],
                'order' => 240,
            ],
            'warehouse_location' => [
                'title' => '库位管理',
                'icon' => 'fa-map',
                'route' => 'warehouse.locations',
                'permission' => 'warehouse.view',
                'roles' => ['warehouse_manager'],
                'order' => 241,
            ],
            'warehouse_stock_warning' => [
                'title' => '库存预警',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'warehouse.stock-warning',
                'permission' => 'inventory.view',
                'roles' => ['warehouse_manager'],
                'order' => 242,
            ],
            'warehouse_inbound_order' => [
                'title' => '入库单',
                'icon' => 'fa-arrow-down',
                'route' => 'warehouse.inbound',
                'permission' => 'inventory.adjust',
                'roles' => ['warehouse_manager'],
                'order' => 243,
            ],
            'warehouse_outbound_order' => [
                'title' => '出库单',
                'icon' => 'fa-arrow-up',
                'route' => 'warehouse.outbound',
                'permission' => 'inventory.adjust',
                'roles' => ['warehouse_manager'],
                'order' => 244,
            ],
            'warehouse_stocktake_order' => [
                'title' => '盘点单',
                'icon' => 'fa-clipboard',
                'route' => 'warehouse.stocktake',
                'permission' => 'inventory.stocktake',
                'roles' => ['warehouse_manager'],
                'order' => 245,
            ],
            'warehouse_stock_adjust' => [
                'title' => '库存调整申请',
                'icon' => 'fa-exchange',
                'route' => 'warehouse.stock-adjust',
                'permission' => 'inventory.adjust',
                'roles' => ['warehouse_manager'],
                'order' => 246,
            ],
            'warehouse_inventory_doc' => [
                'title' => '库存单据',
                'icon' => 'fa-file',
                'route' => 'warehouse.inventory-doc',
                'permission' => 'inventory.view',
                'roles' => ['warehouse_manager'],
                'order' => 247,
            ],
            'warehouse_return_receive' => [
                'title' => '待收货退货',
                'icon' => 'fa-inbox',
                'route' => 'warehouse.return.receive',
                'permission' => 'return.receive',
                'roles' => ['warehouse_manager'],
                'order' => 248,
            ],
            'warehouse_return_inspect' => [
                'title' => '待质检退货',
                'icon' => 'fa-search',
                'route' => 'warehouse.return.inspect',
                'permission' => 'return.inspect',
                'roles' => ['warehouse_manager'],
                'order' => 249,
            ],
            'warehouse_inventory_turnover' => [
                'title' => '库存周转',
                'icon' => 'fa-sync',
                'route' => 'warehouse.inventory.turnover',
                'permission' => 'report.stock',
                'roles' => ['warehouse_manager', 'data_analyst'],
                'order' => 250,
            ],
            'warehouse_stock_age' => [
                'title' => '库龄分析',
                'icon' => 'fa-hourglass',
                'route' => 'warehouse.stock.age',
                'permission' => 'report.stock',
                'roles' => ['warehouse_manager', 'data_analyst'],
                'order' => 251,
            ],
            'fulfillment_assignment_pending' => [
                'title' => '待分配作业',
                'icon' => 'fa-list',
                'route' => 'fulfillment.assignments.index',
                'permission' => 'fulfillment.view',
                'roles' => ['warehouse_manager', 'order_manager'],
                'order' => 252,
            ],

            // ========== 仓库岗位-拣货员（V3.1补全） ==========
            'pick_task_progress' => [
                'title' => '执行中任务',
                'icon' => 'fa-play-circle',
                'route' => 'pick.tasks.progress',
                'permission' => 'pick.task.view',
                'roles' => ['picker'],
                'order' => 253,
            ],
            'pick_task_completed' => [
                'title' => '已完成任务',
                'icon' => 'fa-check-circle',
                'route' => 'pick.tasks.completed',
                'permission' => 'pick.task.view',
                'roles' => ['picker'],
                'order' => 254,
            ],

            // ========== 仓库岗位-复核员（V3.1补全） ==========
            'check_exception' => [
                'title' => '复核异常',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'check.tasks.exception',
                'permission' => 'check.task.view',
                'roles' => ['checker'],
                'order' => 255,
            ],
            'check_completed' => [
                'title' => '已完成复核',
                'icon' => 'fa-check-circle',
                'route' => 'check.tasks.completed',
                'permission' => 'check.task.view',
                'roles' => ['checker'],
                'order' => 256,
            ],
            'check_record' => [
                'title' => '复核记录',
                'icon' => 'fa-history',
                'route' => 'check.tasks.record',
                'permission' => 'check.task.view',
                'roles' => ['checker'],
                'order' => 257,
            ],

            // ========== 仓库岗位-打包员（V3.1补全） ==========
            'pack_material_select' => [
                'title' => '包材选择',
                'icon' => 'fa-cubes',
                'route' => 'pack.tasks.material',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 258,
            ],
            'pack_weight_input' => [
                'title' => '称重录入',
                'icon' => 'fa-balance-scale',
                'route' => 'pack.tasks.weight',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 259,
            ],
            'pack_parcel_generate' => [
                'title' => '包裹生成',
                'icon' => 'fa-box',
                'route' => 'pack.tasks.parcel',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 260,
            ],
            'pack_exception' => [
                'title' => '打包异常',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'pack.tasks.exception',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 261,
            ],
            'pack_completed' => [
                'title' => '已完成打包',
                'icon' => 'fa-check-circle',
                'route' => 'pack.tasks.completed',
                'permission' => 'pack.task.view',
                'roles' => ['packer'],
                'order' => 262,
            ],

            // ========== 物流专员扩展（V3.1补全） ==========
            'logistics_bill' => [
                'title' => '物流账单',
                'icon' => 'fa-file-text',
                'route' => 'logistics.bill',
                'permission' => 'logistics.bill.view',
                'roles' => ['logistics_specialist'],
                'order' => 263,
            ],
            'logistics_ship_callback' => [
                'title' => '发货回传',
                'icon' => 'fa-paper-plane',
                'route' => 'logistics.ship.callback',
                'permission' => 'logistics.callback',
                'roles' => ['logistics_specialist'],
                'order' => 264,
            ],
            'logistics_batch_callback' => [
                'title' => '批量回传',
                'icon' => 'fa-upload',
                'route' => 'logistics.batch.callback',
                'permission' => 'logistics.callback',
                'roles' => ['logistics_specialist'],
                'order' => 265,
            ],
            'logistics_on_time' => [
                'title' => '发货及时率',
                'icon' => 'fa-clock',
                'route' => 'logistics.on-time',
                'permission' => 'logistics.view',
                'roles' => ['logistics_specialist'],
                'order' => 266,
            ],
            'logistics_lead_time' => [
                'title' => '物流时效',
                'icon' => 'fa-hourglass-half',
                'route' => 'logistics.lead-time',
                'permission' => 'logistics.view',
                'roles' => ['logistics_specialist'],
                'order' => 267,
            ],

            // ========== 售后主管扩展（V3.1补全） ==========
            'aftersale_exchange_resend' => [
                'title' => '换货与补发',
                'icon' => 'fa-exchange',
                'route' => 'aftersale.exchange',
                'permission' => 'after_sale.handle',
                'roles' => ['aftersale_manager'],
                'order' => 268,
            ],
            'aftersale_dispute' => [
                'title' => '售后争议',
                'icon' => 'fa-gavel',
                'route' => 'aftersale.dispute',
                'permission' => 'after_sale.handle',
                'roles' => ['aftersale_manager'],
                'order' => 269,
            ],
            'aftersale_operation_log' => [
                'title' => '售后操作记录',
                'icon' => 'fa-history',
                'route' => 'aftersale.operation.log',
                'permission' => 'after_sale.view',
                'roles' => ['aftersale_manager'],
                'order' => 270,
            ],

            // ========== 财务人员/财务主管扩展（V3.1补全） ==========
            'finance_refund_flow' => [
                'title' => '退款流水',
                'icon' => 'fa-list',
                'route' => 'finance.refund.flow',
                'permission' => 'finance.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 271,
            ],
            'finance_platform_settlement' => [
                'title' => '平台结算',
                'icon' => 'fa-money',
                'route' => 'finance.platform.settlement',
                'permission' => 'finance.settle',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 272,
            ],
            'finance_settlement_detail' => [
                'title' => '结算明细',
                'icon' => 'fa-list-alt',
                'route' => 'finance.settlement.detail',
                'permission' => 'finance.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 273,
            ],
            'finance_cost_allocation' => [
                'title' => '费用分摊',
                'icon' => 'fa-percent',
                'route' => 'finance.cost.allocation',
                'permission' => 'finance.view',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 274,
            ],
            'finance_reconcile_task' => [
                'title' => '对账任务',
                'icon' => 'fa-tasks',
                'route' => 'finance.reconcile.task',
                'permission' => 'finance.reconcile',
                'roles' => ['finance_staff', 'finance_manager'],
                'order' => 275,
            ],
            'finance_settlement_audit' => [
                'title' => '结算审核',
                'icon' => 'fa-check-circle',
                'route' => 'finance.settlement.audit',
                'permission' => 'finance.settle',
                'roles' => ['finance_manager'],
                'order' => 276,
            ],
            'finance_cost_audit' => [
                'title' => '费用分摊审核',
                'icon' => 'fa-check',
                'route' => 'finance.cost.audit',
                'permission' => 'finance.view',
                'roles' => ['finance_manager'],
                'order' => 277,
            ],
            'finance_big_diff_approval' => [
                'title' => '大额差异审批',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'finance.diff.approval',
                'permission' => 'finance.reconcile',
                'roles' => ['finance_manager'],
                'order' => 278,
            ],

            // ========== 采购员/供应链主管扩展（V3.1补全） ==========
            'procurement_order_detail' => [
                'title' => '采购订单明细',
                'icon' => 'fa-list',
                'route' => 'procurement.orders.index',
                'permission' => 'procurement.order.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 279,
            ],
            'procurement_safe_stock' => [
                'title' => '安全库存',
                'icon' => 'fa-shield',
                'route' => 'procurement.safe-stocks.index',
                'permission' => 'inventory.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 280,
            ],
            'procurement_in_transit' => [
                'title' => '在途库存',
                'icon' => 'fa-truck',
                'route' => 'procurement.arrivals.index',
                'permission' => 'procurement.arrival',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 281,
            ],
            'procurement_approval' => [
                'title' => '采购审批',
                'icon' => 'fa-check',
                'route' => 'procurement.approvals.index',
                'permission' => 'procurement.approval',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 282,
            ],
            'procurement_supplier_eval' => [
                'title' => '供应商评估',
                'icon' => 'fa-star',
                'route' => 'procurement.suppliers.index',
                'permission' => 'supplier.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 283,
            ],
            'procurement_safe_stock_config' => [
                'title' => '安全库存配置',
                'icon' => 'fa-cog',
                'route' => 'procurement.safe-stocks.index',
                'permission' => 'inventory.view',
                'roles' => ['procurement_staff', 'procurement_manager'],
                'order' => 284,
            ],

            // ========== 数据分析员扩展（V3.1补全） ==========
            'report_order' => [
                'title' => '订单报表',
                'icon' => 'fa-shopping-cart',
                'route' => 'report.order',
                'permission' => 'report.sales',
                'roles' => ['data_analyst'],
                'order' => 285,
            ],
            'report_aftersale' => [
                'title' => '售后报表',
                'icon' => 'fa-life-ring',
                'route' => 'report.aftersale',
                'permission' => 'report.aftersale',
                'roles' => ['operation_manager', 'data_analyst'],
                'order' => 286,
            ],
            'report_shop_analysis' => [
                'title' => '店铺分析',
                'icon' => 'fa-store',
                'route' => 'report.shop.analysis',
                'permission' => 'report.sales',
                'roles' => ['data_analyst'],
                'order' => 287,
            ],
            'report_sku_analysis' => [
                'title' => 'SKU 分析',
                'icon' => 'fa-cubes',
                'route' => 'report.sku.analysis',
                'permission' => 'report.sales',
                'roles' => ['data_analyst'],
                'order' => 288,
            ],
            'report_inventory_turnover' => [
                'title' => '库存周转',
                'icon' => 'fa-sync',
                'route' => 'report.inventory.turnover',
                'permission' => 'report.stock',
                'roles' => ['data_analyst'],
                'order' => 289,
            ],
            'report_metrics_definition' => [
                'title' => '指标口径',
                'icon' => 'fa-calculator',
                'route' => 'report.metrics.definition',
                'permission' => 'report.sales',
                'roles' => ['data_analyst'],
                'order' => 290,
            ],

            // ========== 只读用户扩展（V3.1补全） ==========
            'readonly_order_report' => [
                'title' => '订单报表',
                'icon' => 'fa-shopping-cart',
                'route' => 'readonly.order.report',
                'permission' => 'report.sales',
                'roles' => ['read_only'],
                'order' => 291,
            ],
            'readonly_listing_view' => [
                'title' => 'Listing 查看',
                'icon' => 'fa-list',
                'route' => 'readonly.listing.view',
                'permission' => 'product.view',
                'roles' => ['read_only'],
                'order' => 292,
            ],
            'readonly_inventory_warning' => [
                'title' => '库存预警',
                'icon' => 'fa-exclamation-triangle',
                'route' => 'readonly.inventory.warning',
                'permission' => 'inventory.view',
                'roles' => ['read_only'],
                'order' => 293,
            ],

            // ========== 营销专员扩展（V3.1补全） ==========
            'marketing_campaign_join' => [
                'title' => '活动报名',
                'icon' => 'fa-sign-in',
                'route' => 'marketing.campaign.join',
                'permission' => 'promotion.edit',
                'roles' => ['marketing_staff'],
                'order' => 294,
            ],
            'marketing_campaign_rule' => [
                'title' => '活动规则',
                'icon' => 'fa-list-alt',
                'route' => 'marketing.campaign.rule',
                'permission' => 'promotion.edit',
                'roles' => ['marketing_staff'],
                'order' => 295,
            ],

        ];
    }

    /**
     * 获取当前用户可见的菜单
     * 严格基于角色和权限过滤
     */
    public static function getUserMenus($user): array
    {
        if (!$user) {
            return [];
        }

        $menus = self::getMenus();
        $visibleMenus = [];

        foreach ($menus as $key => $menu) {
            // 检查当前用户是否拥有菜单所需的角色
            if (!$user->hasRole($menu['roles'])) {
                continue;
            }

            // 检查细粒度权限（如果菜单定义了 permission）
            if (isset($menu['permission']) && !$user->hasPermissionTo($menu['permission'])) {
                continue;
            }

            $visibleMenus[$key] = $menu;
        }

        // 按 order 排序
        uasort($visibleMenus, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $visibleMenus;
    }

    /**
     * 获取菜单分组标题定义（V3.1 优化）
     */
    public static function getMenuGroups(): array
    {
        return [
            // 工作台
            'dashboard' => '工作台',

            // 经营分析
            'sales_report' => '经营分析',
            'stock_report' => '经营分析',
            'product_report' => '经营分析',
            'profit_report' => '经营分析',
            'finance_report' => '经营分析',

            // 企业管理（后台）
            'user_management' => '企业管理',
            'department_management' => '企业管理',
            'role_management' => '企业管理',
            'data_scope_management' => '企业管理',

            // 系统管理（后台）
            'system_settings' => '系统管理',
            'approval_flow_management' => '系统管理',
            'approval_instance_management' => '系统管理',
            'tax_rate_management' => '系统管理',
            'currency_management' => '系统管理',
            'export_task_management' => '系统管理',
            'system_parameters' => '系统管理',
            'feature_switch' => '系统管理',
            'notification_templates' => '系统管理',
            'file_storage' => '系统管理',
            'system_version' => '系统管理',

            // 店铺与平台
            'shop_management' => '店铺与平台',
            'shop_authorize' => '店铺与平台',
            'platform_account_management' => '店铺与平台',
            'platform_capability' => '店铺与平台',

            // 商品中心
            'product_spu' => '商品中心',
            'goods_management' => '商品中心',
            'sku_mapping' => '商品中心',
            'brand_management' => '商品中心',
            'category_management' => '商品中心',
            'product_attribute' => '商品中心',
            'media_library' => '商品中心',

            // 价格中心
            'price_query' => '价格中心',
            'price_rule' => '价格中心',
            'price_calculate' => '价格中心',
            'price_change_request' => '价格中心',
            'price_change_task' => '价格中心',
            'price_change_history' => '价格中心',

            // 订单中心
            'order_all' => '订单中心',
            'order_pending_payment' => '订单中心',
            'order_pending_ship' => '订单中心',
            'order_shipped' => '订单中心',
            'order_completed' => '订单中心',
            'order_cancelled' => '订单中心',
            'order_exception' => '订单中心',

            // 客户中心
            'customer_management' => '客户中心',
            'customer_groups' => '客户中心',

            // 营销中心
            'promotion_management' => '营销中心',
            'coupon_management' => '营销中心',
            'promotion_internal' => '营销中心',
            'promotion_analysis' => '营销中心',

            // 仓储中心
            'warehouse_management' => '仓储中心',
            'inventory_management' => '仓储中心',
            'inventory_flow' => '仓储中心',
            'inventory_in' => '仓储中心',
            'inventory_out' => '仓储中心',
            'inventory_transfer' => '仓储中心',
            'inventory_stocktake' => '仓储中心',
            'inventory_batch_management' => '仓储中心',

            // 履约作业（仓库岗位任务）
            'pick_task_pending' => '履约作业',
            'pick_task_scan' => '履约作业',
            'pick_task_exception' => '履约作业',
            'check_task_pending' => '履约作业',
            'check_task_scan' => '履约作业',
            'pack_task_pending' => '履约作业',
            'pack_task_scan' => '履约作业',
            'shipment_pending' => '履约作业',
            'fulfillment_exception' => '履约作业',

            // 物流中心
            'package_management' => '物流中心',
            'express_management' => '物流中心',
            'shipping_method' => '物流中心',
            'shipping_template' => '物流中心',
            'waybill_management' => '物流中心',
            'logistics_tracking' => '物流中心',
            'logistics_exception' => '物流中心',

            // 售后中心
            'aftersale_management' => '售后中心',
            'aftersale_pending' => '售后中心',
            'refund_records' => '售后中心',
            'refund_approve' => '售后中心',
            'return_receive' => '售后中心',
            'return_inspect' => '售后中心',

            // 财务中心
            'finance_receive' => '财务中心',
            'finance_reconcile' => '财务中心',
            'finance_settle' => '财务中心',
            'finance_transaction' => '财务中心',
            'tax_entity_management' => '财务中心',
            'exchange_rate_management' => '财务中心',

            // 采购中心
            'procurement_plan' => '采购中心',
            'procurement_request' => '采购中心',
            'procurement_order' => '采购中心',
            'supplier_management' => '采购中心',
            'procurement_arrival' => '采购中心',
            'procurement_inbound' => '采购中心',
            'procurement_report' => '采购中心',

            // 系统运维
            'sync_task' => '系统运维',
            'sync_log' => '系统运维',
            'sync_cursor_management' => '系统运维',
            'webhook_log' => '系统运维',
            'api_log' => '系统运维',
            'event_log' => '系统运维',
            'exception_management' => '系统运维',
            'event_replay' => '系统运维',
            'queue_monitor' => '系统运维',
            'system_health' => '系统运维',
            'system_alerts' => '系统运维',

            // 安全审计
            'operation_log' => '安全审计',
            'login_log' => '安全审计',
            'permission_change_log' => '安全审计',
            'sensitive_access_log' => '安全审计',
            'security_event' => '安全审计',
            'security_audit_report' => '安全审计',
            'audit_export_log' => '安全审计',
            'approval_flow_record' => '安全审计',

            // 安全中心
            'login_failures' => '安全中心',
            'session_management' => '安全中心',
            'device_management' => '安全中心',
            'ip_whitelist' => '安全中心',
            'mfa_policy' => '安全中心',
            'password_policy' => '安全中心',
            'account_lockout' => '安全中心',

            // 授权运维（店铺授权管理员）
            'auth_status' => '授权运维',
            'token_status' => '授权运维',
            'auth_operation_log' => '授权运维',
            'auth_failure_log' => '授权运维',
            'token_refresh_log' => '授权运维',
            'auth_exception' => '授权运维',

            // 系统运维扩展
            'shop_connection_status' => '系统运维',
            'api_health_check' => '系统运维',
            'sync_failed_tasks' => '系统运维',
            'sync_task_retry' => '系统运维',
            'webhook_failures' => '系统运维',
            'rate_limit_monitor' => '系统运维',

            // 审计追溯中心
            'product_change_log' => '审计追溯中心',
            'order_status_log' => '审计追溯中心',
            'reconciliation_diff_log' => '审计追溯中心',

            // 运营管理
            'sales_report_dashboard' => '运营管理',
            'aftersale_report' => '运营管理',
            'product_publish_approval' => '运营管理',
            'price_change_approval' => '运营管理',
            'order_cancel_approval' => '运营管理',
            'inventory_adjust_approval' => '运营管理',
            'refund_approval_page' => '运营管理',
            'shop_overview' => '运营管理',
            'inventory_warning' => '运营管理',
            'ship_on_time_rate' => '运营管理',
            'aftersale_rate_analysis' => '运营管理',
            'exception_order_overview' => '运营管理',

            // 平台运营
            'listing_management' => '平台运营',
            'platform_product_publish' => '平台运营',
            'publish_task' => '平台运营',
            'platform_category_mapping' => '平台运营',
            'platform_attribute' => '平台运营',

            // 商品中心
            'sku_management' => '商品中心',
            'product_version' => '商品中心',
            'product_import' => '商品中心',
            'product_task_pending_edit' => '商品中心',
            'product_task_returned' => '商品中心',
            'product_task_exception' => '商品中心',

            // 商品审核
            'product_review_pending' => '商品审核',
            'product_review_record' => '商品审核',
            'product_change_review' => '商品审核',
            'product_spu_detail' => '商品审核',

            // 订单中心
            'order_pending_fulfill' => '订单中心',
            'order_split' => '订单中心',
            'order_merge' => '订单中心',
            'order_export' => '订单中心',
            'warehouse_assignment' => '订单中心',
            'fulfillment_order' => '订单中心',
            'order_stock_shortage' => '订单中心',

            // 客户中心
            'customer_tag' => '客户中心',
            'customer_privacy_request' => '客户中心',
            'aftersale_apply' => '客户中心',
            'aftersale_supplement' => '客户中心',
            'aftersale_progress' => '客户中心',
            'aftersale_communication' => '客户中心',

            // 仓储中心
            'warehouse_zone' => '仓储中心',
            'warehouse_location' => '仓储中心',
            'warehouse_stock_warning' => '仓储中心',
            'warehouse_inbound_order' => '仓储中心',
            'warehouse_outbound_order' => '仓储中心',
            'warehouse_stocktake_order' => '仓储中心',
            'warehouse_stock_adjust' => '仓储中心',
            'warehouse_inventory_doc' => '仓储中心',
            'warehouse_return_receive' => '仓储中心',
            'warehouse_return_inspect' => '仓储中心',
            'warehouse_inventory_turnover' => '仓储中心',
            'warehouse_stock_age' => '仓储中心',
            'fulfillment_assignment_pending' => '仓储中心',

            // 拣货任务
            'pick_task_progress' => '履约作业',
            'pick_task_completed' => '履约作业',

            // 复核任务
            'check_exception' => '履约作业',
            'check_completed' => '履约作业',
            'check_record' => '履约作业',

            // 打包任务
            'pack_material_select' => '履约作业',
            'pack_weight_input' => '履约作业',
            'pack_parcel_generate' => '履约作业',
            'pack_exception' => '履约作业',
            'pack_completed' => '履约作业',

            // 物流包裹
            'logistics_bill' => '物流包裹',
            'logistics_ship_callback' => '物流包裹',
            'logistics_batch_callback' => '物流包裹',
            'logistics_on_time' => '物流包裹',
            'logistics_lead_time' => '物流包裹',

            // 售后中心
            'aftersale_exchange_resend' => '售后中心',
            'aftersale_dispute' => '售后中心',
            'aftersale_operation_log' => '售后中心',

            // 财务中心
            'finance_refund_flow' => '财务中心',
            'finance_platform_settlement' => '财务中心',
            'finance_settlement_detail' => '财务中心',
            'finance_cost_allocation' => '财务中心',
            'finance_reconcile_task' => '财务中心',
            'finance_settlement_audit' => '财务中心',
            'finance_cost_audit' => '财务中心',
            'finance_big_diff_approval' => '财务中心',

            // 采购中心
            'procurement_order_detail' => '采购中心',
            'procurement_safe_stock' => '采购中心',
            'procurement_in_transit' => '采购中心',
            'procurement_approval' => '采购中心',
            'procurement_supplier_eval' => '采购中心',
            'procurement_safe_stock_config' => '采购中心',

            // 报表中心
            'report_order' => '报表中心',
            'report_aftersale' => '报表中心',
            'report_shop_analysis' => '报表中心',
            'report_sku_analysis' => '报表中心',
            'report_inventory_turnover' => '报表中心',
            'report_metrics_definition' => '报表中心',

            // 只读视图
            'readonly_order_report' => '报表中心',
            'readonly_listing_view' => '商品中心',
            'readonly_inventory_warning' => '仓储中心',

            // 营销中心
            'marketing_campaign_join' => '营销中心',
            'marketing_campaign_rule' => '营销中心',
        ];
    }

    /**
     * 获取当前用户可见的菜单分组
     */
    public static function getUserMenuGroups($user): array
    {
        $menus = self::getUserMenus($user);
        $groups = self::getMenuGroups();

        $groupedMenus = [];
        $currentGroup = '';

        foreach ($menus as $key => $menu) {
            $menuGroup = self::findMenuGroup($key, $groups);

            if ($menuGroup !== $currentGroup) {
                $currentGroup = $menuGroup;
                $groupedMenus[$currentGroup] = [];
            }

            $groupedMenus[$currentGroup][$key] = $menu;
        }

        return $groupedMenus;
    }

    /**
     * 查找菜单所属分组
     */
    protected static function findMenuGroup(string $menuKey, array $groups): string
    {
        return $groups[$menuKey] ?? '其他';
    }

    /**
     * 获取角色的中文名称映射
     * 用于页面标题等需要显示中文角色名的场景
     */
    public static function getRoleNameMap(): array
    {
        return [
            'super_admin' => '超级管理员',
            'tenant_admin' => '租户管理员',
            'security_admin' => '安全管理员',
            'shop_auth_admin' => '店铺授权管理员',
            'operation_manager' => '运营主管',
            'platform_operator' => '平台运营人员',
            'product_admin' => '商品管理员',
            'product_reviewer' => '商品审核员',
            'pricing_staff' => '定价专员',
            'order_manager' => '订单主管',
            'customer_service' => '客服',
            'warehouse_manager' => '仓库主管',
            'picker' => '拣货员',
            'checker' => '复核员',
            'packer' => '打包员',
            'logistics_specialist' => '物流专员',
            'aftersale_manager' => '售后主管',
            'finance_staff' => '财务人员',
            'finance_manager' => '财务主管',
            'procurement_staff' => '采购专员',
            'data_analyst' => '数据分析师',
            'integration_ops' => '集成运维',
            'auditor' => '审计员',
            'read_only' => '只读用户',
            'marketing_staff' => '营销专员',
            'procurement_manager' => '采购主管',
        ];
    }

    /**
     * 根据角色英文名获取中文名称
     */
    public static function getRoleChineseName(string $roleName): string
    {
        $map = self::getRoleNameMap();
        return $map[$roleName] ?? $roleName;
    }
}