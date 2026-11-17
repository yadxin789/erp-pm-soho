<?php

/*
| SOHO 模块菜单（TC-08 由 docs/menu/baseline-menu.json 生成，勿手改）
| key / group / label / icon / route / permission / roles / order / source
| route 为合并后真实路由名；permission 已对照 RBAC 库校验；roles 为模块内角色白名单（null=不限）
*/

return array (
  0 => 
  array (
    'key' => 'soho.dashboard',
    'group' => '工作台与报表',
    'label' => '首页',
    'icon' => 'fa-solid fa-home',
    'route' => 'soho.dashboard',
    'permission' => 'soho.dashboard.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
      2 => 'soho.security_admin',
      3 => 'soho.shop_auth_admin',
      4 => 'soho.operation_manager',
      5 => 'soho.platform_operator',
      6 => 'soho.product_admin',
      7 => 'soho.product_reviewer',
      8 => 'soho.pricing_staff',
      9 => 'soho.order_manager',
      10 => 'soho.customer_service',
      11 => 'soho.warehouse_manager',
      12 => 'soho.picker',
      13 => 'soho.checker',
      14 => 'soho.packer',
      15 => 'soho.logistics_specialist',
      16 => 'soho.aftersale_manager',
      17 => 'soho.finance_staff',
      18 => 'soho.finance_manager',
      19 => 'soho.procurement_staff',
      20 => 'soho.data_analyst',
      21 => 'soho.integration_ops',
      22 => 'soho.auditor',
      23 => 'soho.read_only',
      24 => 'soho.marketing_staff',
      25 => 'soho.procurement_manager',
    ),
    'order' => 1,
    'source' => 'dashboard',
    'module' => 'soho',
    'sort' => 1,
    'children' => 
    array (
    ),
  ),
  1 => 
  array (
    'key' => 'soho.sales_report',
    'group' => '工作台与报表',
    'label' => '销售报表',
    'icon' => 'fa-solid fa-line-chart',
    'route' => 'soho.sales.report',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.operation_manager',
      2 => 'soho.finance_manager',
      3 => 'soho.pricing_staff',
      4 => 'soho.data_analyst',
      5 => 'soho.auditor',
      6 => 'soho.read_only',
      7 => 'soho.marketing_staff',
    ),
    'order' => 10,
    'source' => 'sales.report',
    'module' => 'soho',
    'sort' => 10,
    'children' => 
    array (
    ),
  ),
  2 => 
  array (
    'key' => 'soho.stock_report',
    'group' => '工作台与报表',
    'label' => '库存报表',
    'icon' => 'fa-solid fa-file-text',
    'route' => 'soho.report.inventory',
    'permission' => 'soho.report.stock',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.data_analyst',
      2 => 'soho.auditor',
      3 => 'soho.read_only',
    ),
    'order' => 11,
    'source' => 'report.inventory',
    'module' => 'soho',
    'sort' => 11,
    'children' => 
    array (
    ),
  ),
  3 => 
  array (
    'key' => 'soho.product_report',
    'group' => '工作台与报表',
    'label' => '商品报表',
    'icon' => 'fa-solid fa-bar-chart',
    'route' => 'soho.product.ranking',
    'permission' => 'soho.report.product',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.operation_manager',
      2 => 'soho.product_admin',
      3 => 'soho.product_reviewer',
      4 => 'soho.platform_operator',
      5 => 'soho.pricing_staff',
      6 => 'soho.data_analyst',
      7 => 'soho.auditor',
      8 => 'soho.read_only',
      9 => 'soho.marketing_staff',
    ),
    'order' => 12,
    'source' => 'product.ranking',
    'module' => 'soho',
    'sort' => 12,
    'children' => 
    array (
    ),
  ),
  4 => 
  array (
    'key' => 'soho.profit_report',
    'group' => '工作台与报表',
    'label' => '利润报表',
    'icon' => 'fa-solid fa-dollar',
    'route' => 'soho.report.profit',
    'permission' => 'soho.report.profit',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.operation_manager',
      2 => 'soho.finance_manager',
      3 => 'soho.pricing_staff',
      4 => 'soho.data_analyst',
      5 => 'soho.auditor',
    ),
    'order' => 13,
    'source' => 'report.profit',
    'module' => 'soho',
    'sort' => 13,
    'children' => 
    array (
    ),
  ),
  5 => 
  array (
    'key' => 'soho.finance_report',
    'group' => '工作台与报表',
    'label' => '财务报表',
    'icon' => 'fa-solid fa-money',
    'route' => 'soho.report.profit',
    'permission' => 'soho.report.profit',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.operation_manager',
      2 => 'soho.finance_manager',
      3 => 'soho.pricing_staff',
      4 => 'soho.data_analyst',
      5 => 'soho.auditor',
    ),
    'order' => 14,
    'source' => 'report.profit',
    'module' => 'soho',
    'sort' => 14,
    'children' => 
    array (
    ),
  ),
  6 => 
  array (
    'key' => 'soho.user_management',
    'group' => '系统与平台',
    'label' => '用户管理',
    'icon' => 'fa-solid fa-users',
    'route' => 'soho.admin.users.index',
    'permission' => 'soho.user.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 20,
    'source' => 'admin.users.index',
    'module' => 'soho',
    'sort' => 20,
    'children' => 
    array (
    ),
  ),
  7 => 
  array (
    'key' => 'soho.department_management',
    'group' => '系统与平台',
    'label' => '部门管理',
    'icon' => 'fa-solid fa-building',
    'route' => 'soho.admin.departments.index',
    'permission' => 'soho.department.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 21,
    'source' => 'admin.departments.index',
    'module' => 'soho',
    'sort' => 21,
    'children' => 
    array (
    ),
  ),
  8 => 
  array (
    'key' => 'soho.role_management',
    'group' => '系统与平台',
    'label' => '角色管理',
    'icon' => 'fa-solid fa-key',
    'route' => 'soho.admin.roles.index',
    'permission' => 'soho.role.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 22,
    'source' => 'admin.roles.index',
    'module' => 'soho',
    'sort' => 22,
    'children' => 
    array (
    ),
  ),
  9 => 
  array (
    'key' => 'soho.data_scope_management',
    'group' => '系统与平台',
    'label' => '数据权限',
    'icon' => 'fa-solid fa-shield',
    'route' => 'soho.data_scopes.index',
    'permission' => 'soho.data_scope.manage',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 23,
    'source' => 'data_scopes.index',
    'module' => 'soho',
    'sort' => 23,
    'children' => 
    array (
    ),
  ),
  10 => 
  array (
    'key' => 'soho.system_settings',
    'group' => '系统与平台',
    'label' => '系统设置',
    'icon' => 'fa-solid fa-cog',
    'route' => 'soho.system.settings',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
      2 => 'soho.security_admin',
    ),
    'order' => 30,
    'source' => 'system.settings',
    'module' => 'soho',
    'sort' => 30,
    'children' => 
    array (
    ),
  ),
  11 => 
  array (
    'key' => 'soho.approval_flow_management',
    'group' => '系统与平台',
    'label' => '审批流管理',
    'icon' => 'fa-solid fa-check-square',
    'route' => 'soho.approval_flows.index',
    'permission' => 'soho.approval_flow.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 31,
    'source' => 'approval_flows.index',
    'module' => 'soho',
    'sort' => 31,
    'children' => 
    array (
    ),
  ),
  12 => 
  array (
    'key' => 'soho.approval_instance_management',
    'group' => '系统与平台',
    'label' => '审批实例',
    'icon' => 'fa-solid fa-hourglass-half',
    'route' => 'soho.approval_instances.index',
    'permission' => 'soho.approval.instance.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 32,
    'source' => 'approval_instances.index',
    'module' => 'soho',
    'sort' => 32,
    'children' => 
    array (
    ),
  ),
  13 => 
  array (
    'key' => 'soho.tax_rate_management',
    'group' => '系统与平台',
    'label' => '税率管理',
    'icon' => 'fa-solid fa-percent',
    'route' => 'soho.tax-rate.index',
    'permission' => 'soho.tax_rate.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 33,
    'source' => 'tax-rate.index',
    'module' => 'soho',
    'sort' => 33,
    'children' => 
    array (
    ),
  ),
  14 => 
  array (
    'key' => 'soho.currency_management',
    'group' => '系统与平台',
    'label' => '货币管理',
    'icon' => 'fa-solid fa-dollar',
    'route' => 'soho.currencies.index',
    'permission' => 'soho.currency.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 34,
    'source' => 'currencies.index',
    'module' => 'soho',
    'sort' => 34,
    'children' => 
    array (
    ),
  ),
  15 => 
  array (
    'key' => 'soho.export_task_management',
    'group' => '系统与平台',
    'label' => '导出任务',
    'icon' => 'fa-solid fa-download',
    'route' => 'soho.export_tasks.index',
    'permission' => 'soho.export_task.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 35,
    'source' => 'export_tasks.index',
    'module' => 'soho',
    'sort' => 35,
    'children' => 
    array (
    ),
  ),
  16 => 
  array (
    'key' => 'soho.system_parameters',
    'group' => '系统与平台',
    'label' => '系统参数',
    'icon' => 'fa-solid fa-sliders',
    'route' => 'soho.system.parameters.index',
    'permission' => 'soho.system.config',
    'roles' => 
    array (
      0 => 'soho.super_admin',
    ),
    'order' => 36,
    'source' => 'system.parameters.index',
    'module' => 'soho',
    'sort' => 36,
    'children' => 
    array (
    ),
  ),
  17 => 
  array (
    'key' => 'soho.feature_switch',
    'group' => '系统与平台',
    'label' => '功能开关',
    'icon' => 'fa-solid fa-toggle-on',
    'route' => 'soho.system.features.index',
    'permission' => 'soho.system.config',
    'roles' => 
    array (
      0 => 'soho.super_admin',
    ),
    'order' => 37,
    'source' => 'system.features.index',
    'module' => 'soho',
    'sort' => 37,
    'children' => 
    array (
    ),
  ),
  18 => 
  array (
    'key' => 'soho.notification_templates',
    'group' => '系统与平台',
    'label' => '通知模板',
    'icon' => 'fa-solid fa-bell',
    'route' => 'soho.system.notifications.index',
    'permission' => 'soho.system.config',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 38,
    'source' => 'system.notifications.index',
    'module' => 'soho',
    'sort' => 38,
    'children' => 
    array (
    ),
  ),
  19 => 
  array (
    'key' => 'soho.file_storage',
    'group' => '系统与平台',
    'label' => '文件存储',
    'icon' => 'fa-solid fa-database',
    'route' => 'soho.system.storage.index',
    'permission' => 'soho.system.config',
    'roles' => 
    array (
      0 => 'soho.super_admin',
    ),
    'order' => 39,
    'source' => 'system.storage.index',
    'module' => 'soho',
    'sort' => 39,
    'children' => 
    array (
    ),
  ),
  20 => 
  array (
    'key' => 'soho.system_version',
    'group' => '系统与平台',
    'label' => '系统版本',
    'icon' => 'fa-solid fa-info-circle',
    'route' => 'soho.system.version',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.tenant_admin',
    ),
    'order' => 40,
    'source' => 'system.version',
    'module' => 'soho',
    'sort' => 40,
    'children' => 
    array (
    ),
  ),
  21 => 
  array (
    'key' => 'soho.shop_management',
    'group' => '系统与平台',
    'label' => '店铺管理',
    'icon' => 'fa-solid fa-shopping-cart',
    'route' => 'soho.shop.index',
    'permission' => 'soho.shop.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.shop_auth_admin',
      2 => 'soho.operation_manager',
      3 => 'soho.platform_operator',
      4 => 'soho.integration_ops',
      5 => 'soho.marketing_staff',
    ),
    'order' => 40,
    'source' => 'shop.index',
    'module' => 'soho',
    'sort' => 40,
    'children' => 
    array (
    ),
  ),
  22 => 
  array (
    'key' => 'soho.shop_authorize',
    'group' => '系统与平台',
    'label' => '店铺授权',
    'icon' => 'fa-solid fa-lock',
    'route' => 'soho.shop.authorize',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 41,
    'source' => 'shop.authorize',
    'module' => 'soho',
    'sort' => 41,
    'children' => 
    array (
    ),
  ),
  23 => 
  array (
    'key' => 'soho.platform_account_management',
    'group' => '系统与平台',
    'label' => '平台账号',
    'icon' => 'fa-solid fa-id-badge',
    'route' => 'soho.platform_accounts.index',
    'permission' => 'soho.platform_account.view',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 42,
    'source' => 'platform_accounts.index',
    'module' => 'soho',
    'sort' => 42,
    'children' => 
    array (
    ),
  ),
  24 => 
  array (
    'key' => 'soho.platform_capability',
    'group' => '系统与平台',
    'label' => '平台能力',
    'icon' => 'fa-solid fa-cogs',
    'route' => 'soho.platform_capabilities.index',
    'permission' => 'soho.platform_account.view',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 43,
    'source' => 'platform_capabilities.index',
    'module' => 'soho',
    'sort' => 43,
    'children' => 
    array (
    ),
  ),
  25 => 
  array (
    'key' => 'soho.product_spu',
    'group' => '商品',
    'label' => '商品SPU',
    'icon' => 'fa-solid fa-cube',
    'route' => 'soho.product.index',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.product_reviewer',
      3 => 'soho.platform_operator',
      4 => 'soho.operation_manager',
      5 => 'soho.pricing_staff',
      6 => 'soho.read_only',
    ),
    'order' => 50,
    'source' => 'product.index',
    'module' => 'soho',
    'sort' => 50,
    'children' => 
    array (
    ),
  ),
  26 => 
  array (
    'key' => 'soho.goods_management',
    'group' => '商品',
    'label' => '商品管理',
    'icon' => 'fa-solid fa-box',
    'route' => 'soho.goods.index',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.platform_operator',
      3 => 'soho.operation_manager',
      4 => 'soho.product_reviewer',
      5 => 'soho.pricing_staff',
      6 => 'soho.read_only',
    ),
    'order' => 51,
    'source' => 'goods.index',
    'module' => 'soho',
    'sort' => 51,
    'children' => 
    array (
    ),
  ),
  27 => 
  array (
    'key' => 'soho.sku_mapping',
    'group' => '商品',
    'label' => 'SKU映射',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.platform-sku.index',
    'permission' => 'soho.sku.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.product_reviewer',
      3 => 'soho.platform_operator',
      4 => 'soho.pricing_staff',
      5 => 'soho.operation_manager',
      6 => 'soho.read_only',
    ),
    'order' => 52,
    'source' => 'platform-sku.index',
    'module' => 'soho',
    'sort' => 52,
    'children' => 
    array (
    ),
  ),
  28 => 
  array (
    'key' => 'soho.brand_management',
    'group' => '商品',
    'label' => '品牌管理',
    'icon' => 'fa-solid fa-star',
    'route' => 'soho.brand.index',
    'permission' => 'soho.brand.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.product_reviewer',
      3 => 'soho.operation_manager',
      4 => 'soho.platform_operator',
    ),
    'order' => 53,
    'source' => 'brand.index',
    'module' => 'soho',
    'sort' => 53,
    'children' => 
    array (
    ),
  ),
  29 => 
  array (
    'key' => 'soho.category_management',
    'group' => '商品',
    'label' => '分类管理',
    'icon' => 'fa-solid fa-folder',
    'route' => 'soho.category.index',
    'permission' => 'soho.category.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.product_reviewer',
      3 => 'soho.platform_operator',
      4 => 'soho.operation_manager',
    ),
    'order' => 54,
    'source' => 'category.index',
    'module' => 'soho',
    'sort' => 54,
    'children' => 
    array (
    ),
  ),
  30 => 
  array (
    'key' => 'soho.product_attribute',
    'group' => '商品',
    'label' => '商品属性',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.product-attribute.index',
    'permission' => 'soho.product.attribute.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.platform_operator',
      3 => 'soho.operation_manager',
    ),
    'order' => 55,
    'source' => 'product-attribute.index',
    'module' => 'soho',
    'sort' => 55,
    'children' => 
    array (
    ),
  ),
  31 => 
  array (
    'key' => 'soho.media_library',
    'group' => '商品',
    'label' => '媒体库',
    'icon' => 'fa-solid fa-picture',
    'route' => 'soho.media.index',
    'permission' => 'soho.media.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.product_admin',
      2 => 'soho.platform_operator',
      3 => 'soho.operation_manager',
      4 => 'soho.product_reviewer',
    ),
    'order' => 56,
    'source' => 'media.index',
    'module' => 'soho',
    'sort' => 56,
    'children' => 
    array (
    ),
  ),
  32 => 
  array (
    'key' => 'soho.price_query',
    'group' => '商品',
    'label' => '价格查询',
    'icon' => 'fa-solid fa-search',
    'route' => 'soho.price.query',
    'permission' => 'soho.price.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
      2 => 'soho.procurement_manager',
      3 => 'soho.operation_manager',
    ),
    'order' => 60,
    'source' => 'price.query',
    'module' => 'soho',
    'sort' => 60,
    'children' => 
    array (
    ),
  ),
  33 => 
  array (
    'key' => 'soho.price_rule',
    'group' => '商品',
    'label' => '价格规则',
    'icon' => 'fa-solid fa-list-alt',
    'route' => 'soho.price.rules.index',
    'permission' => 'soho.price.rule.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
    ),
    'order' => 61,
    'source' => 'price.rules.index',
    'module' => 'soho',
    'sort' => 61,
    'children' => 
    array (
    ),
  ),
  34 => 
  array (
    'key' => 'soho.price_calculate',
    'group' => '商品',
    'label' => '价格计算',
    'icon' => 'fa-solid fa-calculator',
    'route' => 'soho.price.calculate',
    'permission' => 'soho.price.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
      2 => 'soho.procurement_manager',
      3 => 'soho.operation_manager',
    ),
    'order' => 62,
    'source' => 'price.calculate',
    'module' => 'soho',
    'sort' => 62,
    'children' => 
    array (
    ),
  ),
  35 => 
  array (
    'key' => 'soho.price_change_request',
    'group' => '商品',
    'label' => '调价申请',
    'icon' => 'fa-solid fa-edit',
    'route' => 'soho.price.change.requests.index',
    'permission' => 'soho.price.change.request',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
      2 => 'soho.platform_operator',
    ),
    'order' => 63,
    'source' => 'price.change.requests.index',
    'module' => 'soho',
    'sort' => 63,
    'children' => 
    array (
    ),
  ),
  36 => 
  array (
    'key' => 'soho.price_change_task',
    'group' => '商品',
    'label' => '调价任务',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'soho.price.change.tasks.index',
    'permission' => 'soho.price.change.execute',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
    ),
    'order' => 64,
    'source' => 'price.change.tasks.index',
    'module' => 'soho',
    'sort' => 64,
    'children' => 
    array (
    ),
  ),
  37 => 
  array (
    'key' => 'soho.price_change_history',
    'group' => '商品',
    'label' => '调价历史',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.price.change.history',
    'permission' => 'soho.price.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.pricing_staff',
      2 => 'soho.procurement_manager',
      3 => 'soho.operation_manager',
    ),
    'order' => 65,
    'source' => 'price.change.history',
    'module' => 'soho',
    'sort' => 65,
    'children' => 
    array (
    ),
  ),
  38 => 
  array (
    'key' => 'soho.order_all',
    'group' => '订单与客户',
    'label' => '全部订单',
    'icon' => 'fa-solid fa-shopping-bag',
    'route' => 'soho.order.index',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 70,
    'source' => 'order.index',
    'module' => 'soho',
    'sort' => 70,
    'children' => 
    array (
    ),
  ),
  39 => 
  array (
    'key' => 'soho.order_pending_payment',
    'group' => '订单与客户',
    'label' => '待付款',
    'icon' => 'fa-solid fa-money',
    'route' => 'soho.order.pending-payment',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 71,
    'source' => 'order.pending-payment',
    'module' => 'soho',
    'sort' => 71,
    'children' => 
    array (
    ),
  ),
  40 => 
  array (
    'key' => 'soho.order_pending_ship',
    'group' => '订单与客户',
    'label' => '待发货',
    'icon' => 'fa-solid fa-clock',
    'route' => 'soho.order.pending-ship',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 72,
    'source' => 'order.pending-ship',
    'module' => 'soho',
    'sort' => 72,
    'children' => 
    array (
    ),
  ),
  41 => 
  array (
    'key' => 'soho.order_shipped',
    'group' => '订单与客户',
    'label' => '已发货',
    'icon' => 'fa-solid fa-truck',
    'route' => 'soho.order.shipped',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 73,
    'source' => 'order.shipped',
    'module' => 'soho',
    'sort' => 73,
    'children' => 
    array (
    ),
  ),
  42 => 
  array (
    'key' => 'soho.order_completed',
    'group' => '订单与客户',
    'label' => '已完成',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.order.completed',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 74,
    'source' => 'order.completed',
    'module' => 'soho',
    'sort' => 74,
    'children' => 
    array (
    ),
  ),
  43 => 
  array (
    'key' => 'soho.order_cancelled',
    'group' => '订单与客户',
    'label' => '已取消',
    'icon' => 'fa-solid fa-ban',
    'route' => 'soho.order.cancelled',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 75,
    'source' => 'order.cancelled',
    'module' => 'soho',
    'sort' => 75,
    'children' => 
    array (
    ),
  ),
  44 => 
  array (
    'key' => 'soho.order_exception',
    'group' => '订单与客户',
    'label' => '异常订单',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.order.exception',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.order_manager',
      2 => 'soho.customer_service',
      3 => 'soho.operation_manager',
      4 => 'soho.read_only',
    ),
    'order' => 76,
    'source' => 'order.exception',
    'module' => 'soho',
    'sort' => 76,
    'children' => 
    array (
    ),
  ),
  45 => 
  array (
    'key' => 'soho.customer_management',
    'group' => '订单与客户',
    'label' => '客户管理',
    'icon' => 'fa-solid fa-user',
    'route' => 'soho.customer.index',
    'permission' => 'soho.customer.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.customer_service',
      2 => 'soho.operation_manager',
    ),
    'order' => 80,
    'source' => 'customer.index',
    'module' => 'soho',
    'sort' => 80,
    'children' => 
    array (
    ),
  ),
  46 => 
  array (
    'key' => 'soho.customer_groups',
    'group' => '订单与客户',
    'label' => '客户分组',
    'icon' => 'fa-solid fa-users',
    'route' => 'soho.customer.groups',
    'permission' => 'soho.customer.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.customer_service',
      2 => 'soho.operation_manager',
    ),
    'order' => 81,
    'source' => 'customer.groups',
    'module' => 'soho',
    'sort' => 81,
    'children' => 
    array (
    ),
  ),
  47 => 
  array (
    'key' => 'soho.promotion_management',
    'group' => '营销',
    'label' => '促销活动',
    'icon' => 'fa-solid fa-bullhorn',
    'route' => 'soho.promotion.index',
    'permission' => 'soho.promotion.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.marketing_staff',
      2 => 'soho.operation_manager',
      3 => 'soho.platform_operator',
    ),
    'order' => 90,
    'source' => 'promotion.index',
    'module' => 'soho',
    'sort' => 90,
    'children' => 
    array (
    ),
  ),
  48 => 
  array (
    'key' => 'soho.coupon_management',
    'group' => '营销',
    'label' => '优惠券管理',
    'icon' => 'fa-solid fa-ticket',
    'route' => 'soho.coupon.index',
    'permission' => 'soho.coupon.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.marketing_staff',
      2 => 'soho.operation_manager',
      3 => 'soho.platform_operator',
    ),
    'order' => 91,
    'source' => 'coupon.index',
    'module' => 'soho',
    'sort' => 91,
    'children' => 
    array (
    ),
  ),
  49 => 
  array (
    'key' => 'soho.promotion_internal',
    'group' => '营销',
    'label' => '内部促销',
    'icon' => 'fa-solid fa-tag',
    'route' => 'soho.promotion.internal',
    'permission' => 'soho.promotion.view',
    'roles' => 
    array (
      0 => 'soho.marketing_staff',
    ),
    'order' => 92,
    'source' => 'promotion.internal',
    'module' => 'soho',
    'sort' => 92,
    'children' => 
    array (
    ),
  ),
  50 => 
  array (
    'key' => 'soho.promotion_analysis',
    'group' => '营销',
    'label' => '促销分析',
    'icon' => 'fa-solid fa-bar-chart',
    'route' => 'soho.promotion.analysis',
    'permission' => 'soho.promotion.view',
    'roles' => 
    array (
      0 => 'soho.marketing_staff',
    ),
    'order' => 93,
    'source' => 'promotion.analysis',
    'module' => 'soho',
    'sort' => 93,
    'children' => 
    array (
    ),
  ),
  51 => 
  array (
    'key' => 'soho.warehouse_management',
    'group' => '库存与仓储',
    'label' => '仓库管理',
    'icon' => 'fa-solid fa-database',
    'route' => 'soho.warehouse.index',
    'permission' => 'soho.warehouse.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.warehouse_manager',
      2 => 'soho.operation_manager',
      3 => 'soho.order_manager',
      4 => 'soho.data_analyst',
      5 => 'soho.read_only',
    ),
    'order' => 100,
    'source' => 'warehouse.index',
    'module' => 'soho',
    'sort' => 100,
    'children' => 
    array (
    ),
  ),
  52 => 
  array (
    'key' => 'soho.inventory_management',
    'group' => '库存与仓储',
    'label' => '库存查询',
    'icon' => 'fa-solid fa-cubes',
    'route' => 'soho.inventory.index',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.warehouse_manager',
      2 => 'soho.operation_manager',
      3 => 'soho.order_manager',
      4 => 'soho.data_analyst',
      5 => 'soho.read_only',
    ),
    'order' => 101,
    'source' => 'inventory.index',
    'module' => 'soho',
    'sort' => 101,
    'children' => 
    array (
    ),
  ),
  53 => 
  array (
    'key' => 'soho.inventory_flow',
    'group' => '库存与仓储',
    'label' => '库存流水',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.inventory.flow',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 102,
    'source' => 'inventory.flow',
    'module' => 'soho',
    'sort' => 102,
    'children' => 
    array (
    ),
  ),
  54 => 
  array (
    'key' => 'soho.inventory_in',
    'group' => '库存与仓储',
    'label' => '入库管理',
    'icon' => 'fa-solid fa-arrow-down',
    'route' => 'soho.inventory.in',
    'permission' => 'soho.inventory.adjust',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 103,
    'source' => 'inventory.in',
    'module' => 'soho',
    'sort' => 103,
    'children' => 
    array (
    ),
  ),
  55 => 
  array (
    'key' => 'soho.inventory_out',
    'group' => '库存与仓储',
    'label' => '出库管理',
    'icon' => 'fa-solid fa-arrow-up',
    'route' => 'soho.inventory.out',
    'permission' => 'soho.inventory.adjust',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 104,
    'source' => 'inventory.out',
    'module' => 'soho',
    'sort' => 104,
    'children' => 
    array (
    ),
  ),
  56 => 
  array (
    'key' => 'soho.inventory_transfer',
    'group' => '库存与仓储',
    'label' => '库存调拨',
    'icon' => 'fa-solid fa-random',
    'route' => 'soho.inventory.transfer',
    'permission' => 'soho.inventory.transfer',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 105,
    'source' => 'inventory.transfer',
    'module' => 'soho',
    'sort' => 105,
    'children' => 
    array (
    ),
  ),
  57 => 
  array (
    'key' => 'soho.inventory_stocktake',
    'group' => '库存与仓储',
    'label' => '库存盘点',
    'icon' => 'fa-solid fa-clipboard',
    'route' => 'soho.inventory.stocktake',
    'permission' => 'soho.inventory.stocktake',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 106,
    'source' => 'inventory.stocktake',
    'module' => 'soho',
    'sort' => 106,
    'children' => 
    array (
    ),
  ),
  58 => 
  array (
    'key' => 'soho.inventory_batch_management',
    'group' => '库存与仓储',
    'label' => '库存批次',
    'icon' => 'fa-solid fa-layer-group',
    'route' => 'soho.inventory.batches',
    'permission' => 'soho.inventory.batch.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
      1 => 'soho.procurement_staff',
    ),
    'order' => 107,
    'source' => 'inventory.batches',
    'module' => 'soho',
    'sort' => 107,
    'children' => 
    array (
    ),
  ),
  59 => 
  array (
    'key' => 'soho.pick_task_pending',
    'group' => '履约与物流',
    'label' => '待拣货任务',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.pick.tasks.index',
    'permission' => 'soho.picking.view',
    'roles' => 
    array (
      0 => 'soho.picker',
    ),
    'order' => 110,
    'source' => 'pick.tasks.index',
    'module' => 'soho',
    'sort' => 110,
    'children' => 
    array (
    ),
  ),
  60 => 
  array (
    'key' => 'soho.pick_task_scan',
    'group' => '履约与物流',
    'label' => '扫码拣货',
    'icon' => 'fa-solid fa-qrcode',
    'route' => 'soho.pick.tasks.scan',
    'permission' => 'soho.picking.execute',
    'roles' => 
    array (
      0 => 'soho.picker',
    ),
    'order' => 111,
    'source' => 'pick.tasks.scan',
    'module' => 'soho',
    'sort' => 111,
    'children' => 
    array (
    ),
  ),
  61 => 
  array (
    'key' => 'soho.pick_task_exception',
    'group' => '履约与物流',
    'label' => '拣货异常',
    'icon' => 'fa-solid fa-exclamation-circle',
    'route' => 'soho.pick.tasks.exception',
    'permission' => 'soho.picking.view',
    'roles' => 
    array (
      0 => 'soho.picker',
    ),
    'order' => 112,
    'source' => 'pick.tasks.exception',
    'module' => 'soho',
    'sort' => 112,
    'children' => 
    array (
    ),
  ),
  62 => 
  array (
    'key' => 'soho.check_task_pending',
    'group' => '履约与物流',
    'label' => '待复核任务',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.check.tasks.index',
    'permission' => 'soho.checking.view',
    'roles' => 
    array (
      0 => 'soho.checker',
    ),
    'order' => 113,
    'source' => 'check.tasks.index',
    'module' => 'soho',
    'sort' => 113,
    'children' => 
    array (
    ),
  ),
  63 => 
  array (
    'key' => 'soho.check_task_scan',
    'group' => '履约与物流',
    'label' => '扫码复核',
    'icon' => 'fa-solid fa-qrcode',
    'route' => 'soho.check.tasks.scan',
    'permission' => 'soho.checking.execute',
    'roles' => 
    array (
      0 => 'soho.checker',
    ),
    'order' => 114,
    'source' => 'check.tasks.scan',
    'module' => 'soho',
    'sort' => 114,
    'children' => 
    array (
    ),
  ),
  64 => 
  array (
    'key' => 'soho.pack_task_pending',
    'group' => '履约与物流',
    'label' => '待打包任务',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.pack.tasks.index',
    'permission' => 'soho.packing.view',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 115,
    'source' => 'pack.tasks.index',
    'module' => 'soho',
    'sort' => 115,
    'children' => 
    array (
    ),
  ),
  65 => 
  array (
    'key' => 'soho.pack_task_scan',
    'group' => '履约与物流',
    'label' => '扫码打包',
    'icon' => 'fa-solid fa-qrcode',
    'route' => 'soho.pack.tasks.scan',
    'permission' => 'soho.packing.execute',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 116,
    'source' => 'pack.tasks.scan',
    'module' => 'soho',
    'sort' => 116,
    'children' => 
    array (
    ),
  ),
  66 => 
  array (
    'key' => 'soho.shipment_pending',
    'group' => '履约与物流',
    'label' => '待发货包裹',
    'icon' => 'fa-solid fa-box',
    'route' => 'soho.shipment.pending',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
      1 => 'soho.warehouse_manager',
    ),
    'order' => 117,
    'source' => 'shipment.pending',
    'module' => 'soho',
    'sort' => 117,
    'children' => 
    array (
    ),
  ),
  67 => 
  array (
    'key' => 'soho.fulfillment_exception',
    'group' => '履约与物流',
    'label' => '作业异常',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.fulfillment.exception',
    'permission' => 'soho.fulfillment.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
      1 => 'soho.warehouse_manager',
    ),
    'order' => 118,
    'source' => 'fulfillment.exception',
    'module' => 'soho',
    'sort' => 118,
    'children' => 
    array (
    ),
  ),
  68 => 
  array (
    'key' => 'soho.package_management',
    'group' => '履约与物流',
    'label' => '包裹管理',
    'icon' => 'fa-solid fa-cubes',
    'route' => 'soho.package.index',
    'permission' => 'soho.package.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
      1 => 'soho.warehouse_manager',
      2 => 'soho.order_manager',
      3 => 'soho.packer',
    ),
    'order' => 120,
    'source' => 'package.index',
    'module' => 'soho',
    'sort' => 120,
    'children' => 
    array (
    ),
  ),
  69 => 
  array (
    'key' => 'soho.express_management',
    'group' => '履约与物流',
    'label' => '快递公司',
    'icon' => 'fa-solid fa-truck',
    'route' => 'soho.express.index',
    'permission' => 'soho.express.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
      1 => 'soho.warehouse_manager',
      2 => 'soho.order_manager',
      3 => 'soho.packer',
    ),
    'order' => 121,
    'source' => 'express.index',
    'module' => 'soho',
    'sort' => 121,
    'children' => 
    array (
    ),
  ),
  70 => 
  array (
    'key' => 'soho.shipping_method',
    'group' => '履约与物流',
    'label' => '配送方式',
    'icon' => 'fa-solid fa-truck',
    'route' => 'soho.shipping-method.index',
    'permission' => 'soho.shipping.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
      1 => 'soho.warehouse_manager',
      2 => 'soho.packer',
    ),
    'order' => 122,
    'source' => 'shipping-method.index',
    'module' => 'soho',
    'sort' => 122,
    'children' => 
    array (
    ),
  ),
  71 => 
  array (
    'key' => 'soho.shipping_template',
    'group' => '履约与物流',
    'label' => '运费模板',
    'icon' => 'fa-solid fa-table',
    'route' => 'soho.shipping-templates.index',
    'permission' => 'soho.shipping.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
      1 => 'soho.warehouse_manager',
      2 => 'soho.packer',
    ),
    'order' => 123,
    'source' => 'shipping-templates.index',
    'module' => 'soho',
    'sort' => 123,
    'children' => 
    array (
    ),
  ),
  72 => 
  array (
    'key' => 'soho.waybill_management',
    'group' => '履约与物流',
    'label' => '面单管理',
    'icon' => 'fa-solid fa-file',
    'route' => 'soho.waybill.index',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 124,
    'source' => 'waybill.index',
    'module' => 'soho',
    'sort' => 124,
    'children' => 
    array (
    ),
  ),
  73 => 
  array (
    'key' => 'soho.logistics_tracking',
    'group' => '履约与物流',
    'label' => '物流轨迹',
    'icon' => 'fa-solid fa-map-marker',
    'route' => 'soho.logistics.tracking',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 125,
    'source' => 'logistics.tracking',
    'module' => 'soho',
    'sort' => 125,
    'children' => 
    array (
    ),
  ),
  74 => 
  array (
    'key' => 'soho.logistics_exception',
    'group' => '履约与物流',
    'label' => '异常物流',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.logistics.exception',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 126,
    'source' => 'logistics.exception',
    'module' => 'soho',
    'sort' => 126,
    'children' => 
    array (
    ),
  ),
  75 => 
  array (
    'key' => 'soho.aftersale_management',
    'group' => '售后',
    'label' => '售后管理',
    'icon' => 'fa-solid fa-life-ring',
    'route' => 'soho.aftersale.index',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.customer_service',
      2 => 'soho.aftersale_manager',
    ),
    'order' => 130,
    'source' => 'aftersale.index',
    'module' => 'soho',
    'sort' => 130,
    'children' => 
    array (
    ),
  ),
  76 => 
  array (
    'key' => 'soho.aftersale_pending',
    'group' => '售后',
    'label' => '待处理售后',
    'icon' => 'fa-solid fa-hourglass',
    'route' => 'soho.aftersale.pending',
    'permission' => 'soho.after_sale.approve',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.customer_service',
      2 => 'soho.aftersale_manager',
    ),
    'order' => 131,
    'source' => 'aftersale.pending',
    'module' => 'soho',
    'sort' => 131,
    'children' => 
    array (
    ),
  ),
  77 => 
  array (
    'key' => 'soho.refund_records',
    'group' => '售后',
    'label' => '退款记录',
    'icon' => 'fa-solid fa-undo',
    'route' => 'soho.aftersale.refunds',
    'permission' => 'soho.after_sale.refund',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.customer_service',
      2 => 'soho.aftersale_manager',
    ),
    'order' => 132,
    'source' => 'aftersale.refunds',
    'module' => 'soho',
    'sort' => 132,
    'children' => 
    array (
    ),
  ),
  78 => 
  array (
    'key' => 'soho.refund_approve',
    'group' => '售后',
    'label' => '退款审批',
    'icon' => 'fa-solid fa-check',
    'route' => 'soho.refund.approve',
    'permission' => 'soho.refund.approve',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 133,
    'source' => 'refund.approve',
    'module' => 'soho',
    'sort' => 133,
    'children' => 
    array (
    ),
  ),
  79 => 
  array (
    'key' => 'soho.return_receive',
    'group' => '售后',
    'label' => '退货收货',
    'icon' => 'fa-solid fa-inbox',
    'route' => 'soho.return.receive',
    'permission' => 'soho.return.receive',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 134,
    'source' => 'return.receive',
    'module' => 'soho',
    'sort' => 134,
    'children' => 
    array (
    ),
  ),
  80 => 
  array (
    'key' => 'soho.return_inspect',
    'group' => '售后',
    'label' => '退货质检',
    'icon' => 'fa-solid fa-search',
    'route' => 'soho.return.inspect',
    'permission' => 'soho.return.inspect',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 135,
    'source' => 'return.inspect',
    'module' => 'soho',
    'sort' => 135,
    'children' => 
    array (
    ),
  ),
  81 => 
  array (
    'key' => 'soho.finance_receive',
    'group' => '财务',
    'label' => '收款记录',
    'icon' => 'fa-solid fa-money',
    'route' => 'soho.finance.receive',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 140,
    'source' => 'finance.receive',
    'module' => 'soho',
    'sort' => 140,
    'children' => 
    array (
    ),
  ),
  82 => 
  array (
    'key' => 'soho.finance_reconcile',
    'group' => '财务',
    'label' => '对账管理',
    'icon' => 'fa-solid fa-balance-scale',
    'route' => 'soho.finance.reconcile',
    'permission' => 'soho.finance.reconcile',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 141,
    'source' => 'finance.reconcile',
    'module' => 'soho',
    'sort' => 141,
    'children' => 
    array (
    ),
  ),
  83 => 
  array (
    'key' => 'soho.finance_settle',
    'group' => '财务',
    'label' => '结算管理',
    'icon' => 'fa-solid fa-calculator',
    'route' => 'soho.finance.settle',
    'permission' => 'soho.finance.settle',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 142,
    'source' => 'finance.settle',
    'module' => 'soho',
    'sort' => 142,
    'children' => 
    array (
    ),
  ),
  84 => 
  array (
    'key' => 'soho.finance_transaction',
    'group' => '财务',
    'label' => '财务流水',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.finance.transactions',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 143,
    'source' => 'finance.transactions',
    'module' => 'soho',
    'sort' => 143,
    'children' => 
    array (
    ),
  ),
  85 => 
  array (
    'key' => 'soho.tax_entity_management',
    'group' => '财务',
    'label' => '税务主体',
    'icon' => 'fa-solid fa-file-alt',
    'route' => 'soho.tax_entities.index',
    'permission' => 'soho.tax_entity.view',
    'roles' => 
    array (
      0 => 'soho.finance_manager',
    ),
    'order' => 144,
    'source' => 'tax_entities.index',
    'module' => 'soho',
    'sort' => 144,
    'children' => 
    array (
    ),
  ),
  86 => 
  array (
    'key' => 'soho.exchange_rate_management',
    'group' => '财务',
    'label' => '汇率管理',
    'icon' => 'fa-solid fa-exchange-alt',
    'route' => 'soho.exchange_rates.index',
    'permission' => 'soho.exchange_rate.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 145,
    'source' => 'exchange_rates.index',
    'module' => 'soho',
    'sort' => 145,
    'children' => 
    array (
    ),
  ),
  87 => 
  array (
    'key' => 'soho.procurement_plan',
    'group' => '采购',
    'label' => '采购计划',
    'icon' => 'fa-solid fa-calendar',
    'route' => 'soho.procurement.plans.index',
    'permission' => 'soho.procurement.plan.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 150,
    'source' => 'procurement.plans.index',
    'module' => 'soho',
    'sort' => 150,
    'children' => 
    array (
    ),
  ),
  88 => 
  array (
    'key' => 'soho.procurement_request',
    'group' => '采购',
    'label' => '采购申请',
    'icon' => 'fa-solid fa-file',
    'route' => 'soho.procurement.requests.index',
    'permission' => 'soho.procurement.request.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 151,
    'source' => 'procurement.requests.index',
    'module' => 'soho',
    'sort' => 151,
    'children' => 
    array (
    ),
  ),
  89 => 
  array (
    'key' => 'soho.procurement_order',
    'group' => '采购',
    'label' => '采购订单',
    'icon' => 'fa-solid fa-file-text',
    'route' => 'soho.procurement.orders.index',
    'permission' => 'soho.procurement.order.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 152,
    'source' => 'procurement.orders.index',
    'module' => 'soho',
    'sort' => 152,
    'children' => 
    array (
    ),
  ),
  90 => 
  array (
    'key' => 'soho.supplier_management',
    'group' => '采购',
    'label' => '供应商管理',
    'icon' => 'fa-solid fa-users',
    'route' => 'soho.procurement.suppliers.index',
    'permission' => 'soho.supplier.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 153,
    'source' => 'procurement.suppliers.index',
    'module' => 'soho',
    'sort' => 153,
    'children' => 
    array (
    ),
  ),
  91 => 
  array (
    'key' => 'soho.procurement_arrival',
    'group' => '采购',
    'label' => '采购到货',
    'icon' => 'fa-solid fa-download',
    'route' => 'soho.procurement.arrivals.index',
    'permission' => 'soho.procurement.arrival',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 154,
    'source' => 'procurement.arrivals.index',
    'module' => 'soho',
    'sort' => 154,
    'children' => 
    array (
    ),
  ),
  92 => 
  array (
    'key' => 'soho.procurement_inbound',
    'group' => '采购',
    'label' => '采购入库协同',
    'icon' => 'fa-solid fa-warehouse',
    'route' => 'soho.procurement.inbounds.index',
    'permission' => 'soho.procurement.inbound',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 155,
    'source' => 'procurement.inbounds.index',
    'module' => 'soho',
    'sort' => 155,
    'children' => 
    array (
    ),
  ),
  93 => 
  array (
    'key' => 'soho.procurement_report',
    'group' => '采购',
    'label' => '采购报表',
    'icon' => 'fa-solid fa-bar-chart',
    'route' => 'soho.report.procurement',
    'permission' => 'soho.report.procurement',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 156,
    'source' => 'report.procurement',
    'module' => 'soho',
    'sort' => 156,
    'children' => 
    array (
    ),
  ),
  94 => 
  array (
    'key' => 'soho.safe_stock',
    'group' => '采购',
    'label' => '安全库存',
    'icon' => 'fa-solid fa-shield',
    'route' => 'soho.procurement.safe-stocks.index',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 157,
    'source' => 'procurement.safe-stocks.index',
    'module' => 'soho',
    'sort' => 157,
    'children' => 
    array (
    ),
  ),
  95 => 
  array (
    'key' => 'soho.sync_task',
    'group' => '集成与运维',
    'label' => '同步任务',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'soho.sync-task.index',
    'permission' => 'soho.sync_task.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.shop_auth_admin',
    ),
    'order' => 160,
    'source' => 'sync-task.index',
    'module' => 'soho',
    'sort' => 160,
    'children' => 
    array (
    ),
  ),
  96 => 
  array (
    'key' => 'soho.sync_log',
    'group' => '集成与运维',
    'label' => '同步日志',
    'icon' => 'fa-solid fa-file',
    'route' => 'soho.log.sync-task.index',
    'permission' => 'soho.log.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.shop_auth_admin',
    ),
    'order' => 161,
    'source' => 'log.sync-task.index',
    'module' => 'soho',
    'sort' => 161,
    'children' => 
    array (
    ),
  ),
  97 => 
  array (
    'key' => 'soho.sync_cursor_management',
    'group' => '集成与运维',
    'label' => '同步游标',
    'icon' => 'fa-solid fa-sync',
    'route' => 'soho.sync_cursors.index',
    'permission' => 'soho.sync_cursor.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
    ),
    'order' => 162,
    'source' => 'sync_cursors.index',
    'module' => 'soho',
    'sort' => 162,
    'children' => 
    array (
    ),
  ),
  98 => 
  array (
    'key' => 'soho.webhook_log',
    'group' => '集成与运维',
    'label' => 'Webhook日志',
    'icon' => 'fa-solid fa-plug',
    'route' => 'soho.log.webhook',
    'permission' => 'soho.log.api',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.super_admin',
    ),
    'order' => 163,
    'source' => 'log.webhook',
    'module' => 'soho',
    'sort' => 163,
    'children' => 
    array (
    ),
  ),
  99 => 
  array (
    'key' => 'soho.api_log',
    'group' => '集成与运维',
    'label' => 'API日志',
    'icon' => 'fa-solid fa-code',
    'route' => 'soho.log.platform-api.index',
    'permission' => 'soho.log.api',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.security_admin',
      2 => 'soho.integration_ops',
    ),
    'order' => 164,
    'source' => 'log.platform-api.index',
    'module' => 'soho',
    'sort' => 164,
    'children' => 
    array (
    ),
  ),
  100 => 
  array (
    'key' => 'soho.event_log',
    'group' => '集成与运维',
    'label' => '事件日志',
    'icon' => 'fa-solid fa-random',
    'route' => 'soho.event-log.index',
    'permission' => 'soho.event.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 165,
    'source' => 'event-log.index',
    'module' => 'soho',
    'sort' => 165,
    'children' => 
    array (
    ),
  ),
  101 => 
  array (
    'key' => 'soho.exception_management',
    'group' => '集成与运维',
    'label' => '异常中心',
    'icon' => 'fa-solid fa-exclamation-circle',
    'route' => 'soho.exceptions.index',
    'permission' => 'soho.exception.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 166,
    'source' => 'exceptions.index',
    'module' => 'soho',
    'sort' => 166,
    'children' => 
    array (
    ),
  ),
  102 => 
  array (
    'key' => 'soho.event_replay',
    'group' => '集成与运维',
    'label' => '事件重放',
    'icon' => 'fa-solid fa-redo',
    'route' => 'soho.event.replay',
    'permission' => 'soho.event.replay',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.super_admin',
    ),
    'order' => 167,
    'source' => 'event.replay',
    'module' => 'soho',
    'sort' => 167,
    'children' => 
    array (
    ),
  ),
  103 => 
  array (
    'key' => 'soho.queue_monitor',
    'group' => '集成与运维',
    'label' => '队列监控',
    'icon' => 'fa-solid fa-list-alt',
    'route' => 'soho.queue.monitor',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 168,
    'source' => 'queue.monitor',
    'module' => 'soho',
    'sort' => 168,
    'children' => 
    array (
    ),
  ),
  104 => 
  array (
    'key' => 'soho.system_health',
    'group' => '集成与运维',
    'label' => '系统健康检查',
    'icon' => 'fa-solid fa-heartbeat',
    'route' => 'soho.system.health',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 169,
    'source' => 'system.health',
    'module' => 'soho',
    'sort' => 169,
    'children' => 
    array (
    ),
  ),
  105 => 
  array (
    'key' => 'soho.system_alerts',
    'group' => '集成与运维',
    'label' => '系统告警',
    'icon' => 'fa-solid fa-bell',
    'route' => 'soho.system.alerts.index',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.integration_ops',
    ),
    'order' => 170,
    'source' => 'system.alerts.index',
    'module' => 'soho',
    'sort' => 170,
    'children' => 
    array (
    ),
  ),
  106 => 
  array (
    'key' => 'soho.operation_log',
    'group' => '集成与运维',
    'label' => '操作日志',
    'icon' => 'fa-solid fa-file-text-o',
    'route' => 'soho.operation-logs.index',
    'permission' => 'soho.log.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.tenant_admin',
      2 => 'soho.auditor',
    ),
    'order' => 170,
    'source' => 'operation-logs.index',
    'module' => 'soho',
    'sort' => 170,
    'children' => 
    array (
    ),
  ),
  107 => 
  array (
    'key' => 'soho.login_log',
    'group' => '集成与运维',
    'label' => '登录日志',
    'icon' => 'fa-solid fa-sign-in',
    'route' => 'soho.log.login',
    'permission' => 'soho.log.audit',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.auditor',
    ),
    'order' => 171,
    'source' => 'log.login',
    'module' => 'soho',
    'sort' => 171,
    'children' => 
    array (
    ),
  ),
  108 => 
  array (
    'key' => 'soho.permission_change_log',
    'group' => '集成与运维',
    'label' => '权限变更日志',
    'icon' => 'fa-solid fa-key',
    'route' => 'soho.log.permission-change',
    'permission' => 'soho.log.audit',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.auditor',
    ),
    'order' => 172,
    'source' => 'log.permission-change',
    'module' => 'soho',
    'sort' => 172,
    'children' => 
    array (
    ),
  ),
  109 => 
  array (
    'key' => 'soho.sensitive_access_log',
    'group' => '集成与运维',
    'label' => '敏感数据访问日志',
    'icon' => 'fa-solid fa-eye-slash',
    'route' => 'soho.log.sensitive-access',
    'permission' => 'soho.log.audit',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.auditor',
    ),
    'order' => 173,
    'source' => 'log.sensitive-access',
    'module' => 'soho',
    'sort' => 173,
    'children' => 
    array (
    ),
  ),
  110 => 
  array (
    'key' => 'soho.security_event',
    'group' => '集成与运维',
    'label' => '安全事件',
    'icon' => 'fa-solid fa-shield',
    'route' => 'soho.security.event',
    'permission' => 'soho.security.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.auditor',
      2 => 'soho.super_admin',
    ),
    'order' => 174,
    'source' => 'security.event',
    'module' => 'soho',
    'sort' => 174,
    'children' => 
    array (
    ),
  ),
  111 => 
  array (
    'key' => 'soho.login_failures',
    'group' => '集成与运维',
    'label' => '登录失败记录',
    'icon' => 'fa-solid fa-ban',
    'route' => 'soho.security.login.failures',
    'permission' => 'soho.security.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 175,
    'source' => 'security.login.failures',
    'module' => 'soho',
    'sort' => 175,
    'children' => 
    array (
    ),
  ),
  112 => 
  array (
    'key' => 'soho.session_management',
    'group' => '集成与运维',
    'label' => '会话管理',
    'icon' => 'fa-solid fa-user-circle',
    'route' => 'soho.security.sessions',
    'permission' => 'soho.security.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 176,
    'source' => 'security.sessions',
    'module' => 'soho',
    'sort' => 176,
    'children' => 
    array (
    ),
  ),
  113 => 
  array (
    'key' => 'soho.device_management',
    'group' => '集成与运维',
    'label' => '设备管理',
    'icon' => 'fa-solid fa-laptop',
    'route' => 'soho.security.devices',
    'permission' => 'soho.security.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 177,
    'source' => 'security.devices',
    'module' => 'soho',
    'sort' => 177,
    'children' => 
    array (
    ),
  ),
  114 => 
  array (
    'key' => 'soho.ip_whitelist',
    'group' => '集成与运维',
    'label' => 'IP白名单',
    'icon' => 'fa-solid fa-map-marker',
    'route' => 'soho.security.ip-whitelist.index',
    'permission' => 'soho.security.ip_whitelist',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 178,
    'source' => 'security.ip-whitelist.index',
    'module' => 'soho',
    'sort' => 178,
    'children' => 
    array (
    ),
  ),
  115 => 
  array (
    'key' => 'soho.mfa_policy',
    'group' => '集成与运维',
    'label' => '二次验证策略',
    'icon' => 'fa-solid fa-key',
    'route' => 'soho.security.mfa',
    'permission' => 'soho.security.config',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 179,
    'source' => 'security.mfa',
    'module' => 'soho',
    'sort' => 179,
    'children' => 
    array (
    ),
  ),
  116 => 
  array (
    'key' => 'soho.password_policy',
    'group' => '集成与运维',
    'label' => '密码策略',
    'icon' => 'fa-solid fa-lock',
    'route' => 'soho.security.password-policy',
    'permission' => 'soho.security.config',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 180,
    'source' => 'security.password-policy',
    'module' => 'soho',
    'sort' => 180,
    'children' => 
    array (
    ),
  ),
  117 => 
  array (
    'key' => 'soho.account_lockout',
    'group' => '集成与运维',
    'label' => '账号锁定记录',
    'icon' => 'fa-solid fa-user-times',
    'route' => 'soho.security.lockouts',
    'permission' => 'soho.security.view',
    'roles' => 
    array (
      0 => 'soho.security_admin',
    ),
    'order' => 181,
    'source' => 'security.lockouts',
    'module' => 'soho',
    'sort' => 181,
    'children' => 
    array (
    ),
  ),
  118 => 
  array (
    'key' => 'soho.security_audit_report',
    'group' => '集成与运维',
    'label' => '审计报告',
    'icon' => 'fa-solid fa-file-pdf-o',
    'route' => 'soho.audit.reports',
    'permission' => 'soho.log.audit',
    'roles' => 
    array (
      0 => 'soho.security_admin',
      1 => 'soho.auditor',
      2 => 'soho.super_admin',
    ),
    'order' => 182,
    'source' => 'audit.reports',
    'module' => 'soho',
    'sort' => 182,
    'children' => 
    array (
    ),
  ),
  119 => 
  array (
    'key' => 'soho.auth_status',
    'group' => '集成与运维',
    'label' => '授权状态',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.shop.authorization-status.index',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 183,
    'source' => 'shop.authorization-status.index',
    'module' => 'soho',
    'sort' => 183,
    'children' => 
    array (
    ),
  ),
  120 => 
  array (
    'key' => 'soho.token_status',
    'group' => '集成与运维',
    'label' => 'Token状态',
    'icon' => 'fa-solid fa-key',
    'route' => 'soho.shop.token-status',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 184,
    'source' => 'shop.token-status',
    'module' => 'soho',
    'sort' => 184,
    'children' => 
    array (
    ),
  ),
  121 => 
  array (
    'key' => 'soho.auth_operation_log',
    'group' => '集成与运维',
    'label' => '授权操作日志',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.shop.authorization-logs.index',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 185,
    'source' => 'shop.authorization-logs.index',
    'module' => 'soho',
    'sort' => 185,
    'children' => 
    array (
    ),
  ),
  122 => 
  array (
    'key' => 'soho.auth_failure_log',
    'group' => '集成与运维',
    'label' => '授权失败记录',
    'icon' => 'fa-solid fa-times',
    'route' => 'soho.shop.authorization-failures.index',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 186,
    'source' => 'shop.authorization-failures.index',
    'module' => 'soho',
    'sort' => 186,
    'children' => 
    array (
    ),
  ),
  123 => 
  array (
    'key' => 'soho.token_refresh_log',
    'group' => '集成与运维',
    'label' => 'Token刷新记录',
    'icon' => 'fa-solid fa-refresh',
    'route' => 'soho.shop.token-refresh-logs.index',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 187,
    'source' => 'shop.token-refresh-logs.index',
    'module' => 'soho',
    'sort' => 187,
    'children' => 
    array (
    ),
  ),
  124 => 
  array (
    'key' => 'soho.auth_exception',
    'group' => '集成与运维',
    'label' => '授权异常',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.shop.authorization-exceptions.index',
    'permission' => 'soho.shop.authorize',
    'roles' => 
    array (
      0 => 'soho.shop_auth_admin',
    ),
    'order' => 188,
    'source' => 'shop.authorization-exceptions.index',
    'module' => 'soho',
    'sort' => 188,
    'children' => 
    array (
    ),
  ),
  125 => 
  array (
    'key' => 'soho.shop_connection_status',
    'group' => '集成与运维',
    'label' => '店铺连接状态',
    'icon' => 'fa-solid fa-plug',
    'route' => 'soho.integration.connection.status',
    'permission' => 'soho.sync_task.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
    ),
    'order' => 189,
    'source' => 'integration.connection.status',
    'module' => 'soho',
    'sort' => 189,
    'children' => 
    array (
    ),
  ),
  126 => 
  array (
    'key' => 'soho.api_health_check',
    'group' => '审计',
    'label' => 'API健康检查',
    'icon' => 'fa-solid fa-heartbeat',
    'route' => 'soho.integration.api.health',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
    ),
    'order' => 190,
    'source' => 'integration.api.health',
    'module' => 'soho',
    'sort' => 190,
    'children' => 
    array (
    ),
  ),
  127 => 
  array (
    'key' => 'soho.sync_failed_tasks',
    'group' => '审计',
    'label' => '失败任务',
    'icon' => 'fa-solid fa-times-circle',
    'route' => 'soho.sync-task.failed',
    'permission' => 'soho.sync_task.execute',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
    ),
    'order' => 191,
    'source' => 'sync-task.failed',
    'module' => 'soho',
    'sort' => 191,
    'children' => 
    array (
    ),
  ),
  128 => 
  array (
    'key' => 'soho.sync_task_retry',
    'group' => '审计',
    'label' => '任务重试',
    'icon' => 'fa-solid fa-repeat',
    'route' => 'soho.sync-task.retry',
    'permission' => 'soho.sync_task.execute',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
    ),
    'order' => 192,
    'source' => 'sync-task.retry',
    'module' => 'soho',
    'sort' => 192,
    'children' => 
    array (
    ),
  ),
  129 => 
  array (
    'key' => 'soho.webhook_failures',
    'group' => '审计',
    'label' => 'Webhook失败记录',
    'icon' => 'fa-solid fa-exclamation-circle',
    'route' => 'soho.log.webhook.failures',
    'permission' => 'soho.log.api',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.super_admin',
    ),
    'order' => 193,
    'source' => 'log.webhook.failures',
    'module' => 'soho',
    'sort' => 193,
    'children' => 
    array (
    ),
  ),
  130 => 
  array (
    'key' => 'soho.rate_limit_monitor',
    'group' => '审计',
    'label' => '限流监控',
    'icon' => 'fa-solid fa-tachometer',
    'route' => 'soho.integration.rate-limit',
    'permission' => 'soho.system.view',
    'roles' => 
    array (
      0 => 'soho.integration_ops',
      1 => 'soho.super_admin',
    ),
    'order' => 194,
    'source' => 'integration.rate-limit',
    'module' => 'soho',
    'sort' => 194,
    'children' => 
    array (
    ),
  ),
  131 => 
  array (
    'key' => 'soho.audit_export_log',
    'group' => '审计',
    'label' => '导出日志',
    'icon' => 'fa-solid fa-file-excel-o',
    'route' => 'soho.audit.export-log',
    'permission' => 'soho.log.audit',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.security_admin',
      2 => 'soho.auditor',
    ),
    'order' => 195,
    'source' => 'audit.export-log',
    'module' => 'soho',
    'sort' => 195,
    'children' => 
    array (
    ),
  ),
  132 => 
  array (
    'key' => 'soho.approval_flow_record',
    'group' => '审计',
    'label' => '审批流记录',
    'icon' => 'fa-solid fa-check-square-o',
    'route' => 'soho.audit.approval-flow',
    'permission' => 'soho.approval_flow.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.auditor',
    ),
    'order' => 196,
    'source' => 'audit.approval-flow',
    'module' => 'soho',
    'sort' => 196,
    'children' => 
    array (
    ),
  ),
  133 => 
  array (
    'key' => 'soho.product_change_log',
    'group' => '审计',
    'label' => '商品变更记录',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.audit.product-change',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.auditor',
      2 => 'soho.operation_manager',
    ),
    'order' => 197,
    'source' => 'audit.product-change',
    'module' => 'soho',
    'sort' => 197,
    'children' => 
    array (
    ),
  ),
  134 => 
  array (
    'key' => 'soho.order_status_log',
    'group' => '审计',
    'label' => '订单状态记录',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.audit.order-status',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.auditor',
      2 => 'soho.operation_manager',
    ),
    'order' => 198,
    'source' => 'audit.order-status',
    'module' => 'soho',
    'sort' => 198,
    'children' => 
    array (
    ),
  ),
  135 => 
  array (
    'key' => 'soho.reconciliation_diff_log',
    'group' => '审计',
    'label' => '对账差异记录',
    'icon' => 'fa-solid fa-columns',
    'route' => 'soho.audit.reconciliation-diff',
    'permission' => 'soho.finance.reconcile',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.auditor',
      2 => 'soho.operation_manager',
    ),
    'order' => 199,
    'source' => 'audit.reconciliation-diff',
    'module' => 'soho',
    'sort' => 199,
    'children' => 
    array (
    ),
  ),
  136 => 
  array (
    'key' => 'soho.sales_report_dashboard',
    'group' => '经营分析',
    'label' => '经营看板',
    'icon' => 'fa-solid fa-dashboard',
    'route' => 'soho.operation.dashboard',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 200,
    'source' => 'operation.dashboard',
    'module' => 'soho',
    'sort' => 200,
    'children' => 
    array (
    ),
  ),
  137 => 
  array (
    'key' => 'soho.aftersale_report',
    'group' => '经营分析',
    'label' => '售后报表',
    'icon' => 'fa-solid fa-life-ring',
    'route' => 'soho.report.aftersale',
    'permission' => 'soho.report.aftersale',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.data_analyst',
    ),
    'order' => 201,
    'source' => 'report.aftersale',
    'module' => 'soho',
    'sort' => 201,
    'children' => 
    array (
    ),
  ),
  138 => 
  array (
    'key' => 'soho.product_publish_approval',
    'group' => '经营分析',
    'label' => '商品发布审批',
    'icon' => 'fa-solid fa-check',
    'route' => 'soho.approval.product-publish.index',
    'permission' => 'soho.product.approve',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.product_admin',
      2 => 'soho.product_reviewer',
      3 => 'soho.super_admin',
    ),
    'order' => 202,
    'source' => 'approval.product-publish.index',
    'module' => 'soho',
    'sort' => 202,
    'children' => 
    array (
    ),
  ),
  139 => 
  array (
    'key' => 'soho.price_change_approval',
    'group' => '经营分析',
    'label' => '调价审批',
    'icon' => 'fa-solid fa-dollar',
    'route' => 'soho.price.change.approvals.index',
    'permission' => 'soho.price.change.approve',
    'roles' => 
    array (
      0 => 'soho.super_admin',
      1 => 'soho.operation_manager',
      2 => 'soho.procurement_manager',
    ),
    'order' => 203,
    'source' => 'price.change.approvals.index',
    'module' => 'soho',
    'sort' => 203,
    'children' => 
    array (
    ),
  ),
  140 => 
  array (
    'key' => 'soho.order_cancel_approval',
    'group' => '经营分析',
    'label' => '订单取消审批',
    'icon' => 'fa-solid fa-ban',
    'route' => 'soho.approval.order-cancel.index',
    'permission' => 'soho.order.approve',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.order_manager',
      2 => 'soho.super_admin',
    ),
    'order' => 204,
    'source' => 'approval.order-cancel.index',
    'module' => 'soho',
    'sort' => 204,
    'children' => 
    array (
    ),
  ),
  141 => 
  array (
    'key' => 'soho.inventory_adjust_approval',
    'group' => '经营分析',
    'label' => '库存调整审批',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.approval.inventory-adjust.index',
    'permission' => 'soho.inventory.approve',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.warehouse_manager',
      2 => 'soho.super_admin',
    ),
    'order' => 205,
    'source' => 'approval.inventory-adjust.index',
    'module' => 'soho',
    'sort' => 205,
    'children' => 
    array (
    ),
  ),
  142 => 
  array (
    'key' => 'soho.refund_approval_page',
    'group' => '经营分析',
    'label' => '售后退款审批',
    'icon' => 'fa-solid fa-undo',
    'route' => 'soho.approval.refund.index',
    'permission' => 'soho.refund.approve',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.aftersale_manager',
      2 => 'soho.super_admin',
    ),
    'order' => 206,
    'source' => 'approval.refund.index',
    'module' => 'soho',
    'sort' => 206,
    'children' => 
    array (
    ),
  ),
  143 => 
  array (
    'key' => 'soho.shop_overview',
    'group' => '经营分析',
    'label' => '店铺经营概览',
    'icon' => 'fa-solid fa-store',
    'route' => 'soho.operation.shop.overview',
    'permission' => 'soho.shop.view',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 207,
    'source' => 'operation.shop.overview',
    'module' => 'soho',
    'sort' => 207,
    'children' => 
    array (
    ),
  ),
  144 => 
  array (
    'key' => 'soho.inventory_warning',
    'group' => '经营分析',
    'label' => '库存预警',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.operation.inventory.warning',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 208,
    'source' => 'operation.inventory.warning',
    'module' => 'soho',
    'sort' => 208,
    'children' => 
    array (
    ),
  ),
  145 => 
  array (
    'key' => 'soho.ship_on_time_rate',
    'group' => '经营分析',
    'label' => '发货及时率',
    'icon' => 'fa-solid fa-clock',
    'route' => 'soho.operation.ship.on-time',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 209,
    'source' => 'operation.ship.on-time',
    'module' => 'soho',
    'sort' => 209,
    'children' => 
    array (
    ),
  ),
  146 => 
  array (
    'key' => 'soho.aftersale_rate_analysis',
    'group' => '经营分析',
    'label' => '售后率分析',
    'icon' => 'fa-solid fa-chart-line',
    'route' => 'soho.operation.aftersale.rate',
    'permission' => 'soho.report.aftersale',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 210,
    'source' => 'operation.aftersale.rate',
    'module' => 'soho',
    'sort' => 210,
    'children' => 
    array (
    ),
  ),
  147 => 
  array (
    'key' => 'soho.exception_order_overview',
    'group' => '经营分析',
    'label' => '异常订单概览',
    'icon' => 'fa-solid fa-exclamation-circle',
    'route' => 'soho.operation.order.exception',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
    ),
    'order' => 211,
    'source' => 'operation.order.exception',
    'module' => 'soho',
    'sort' => 211,
    'children' => 
    array (
    ),
  ),
  148 => 
  array (
    'key' => 'soho.listing_management',
    'group' => '经营分析',
    'label' => 'Listing管理',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.platform.listing',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.platform_operator',
    ),
    'order' => 212,
    'source' => 'platform.listing',
    'module' => 'soho',
    'sort' => 212,
    'children' => 
    array (
    ),
  ),
  149 => 
  array (
    'key' => 'soho.platform_product_publish',
    'group' => '经营分析',
    'label' => '平台商品发布',
    'icon' => 'fa-solid fa-upload',
    'route' => 'soho.platform.product.publish',
    'permission' => 'soho.product.create',
    'roles' => 
    array (
      0 => 'soho.platform_operator',
    ),
    'order' => 213,
    'source' => 'platform.product.publish',
    'module' => 'soho',
    'sort' => 213,
    'children' => 
    array (
    ),
  ),
  150 => 
  array (
    'key' => 'soho.publish_task',
    'group' => '经营分析',
    'label' => '商品发布任务',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'soho.platform.publish.task',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.platform_operator',
    ),
    'order' => 214,
    'source' => 'platform.publish.task',
    'module' => 'soho',
    'sort' => 214,
    'children' => 
    array (
    ),
  ),
  151 => 
  array (
    'key' => 'soho.platform_category_mapping',
    'group' => '经营分析',
    'label' => '分类映射',
    'icon' => 'fa-solid fa-map',
    'route' => 'soho.platform.category.mapping',
    'permission' => 'soho.category.view',
    'roles' => 
    array (
      0 => 'soho.platform_operator',
    ),
    'order' => 215,
    'source' => 'platform.category.mapping',
    'module' => 'soho',
    'sort' => 215,
    'children' => 
    array (
    ),
  ),
  152 => 
  array (
    'key' => 'soho.platform_attribute',
    'group' => '经营分析',
    'label' => '平台属性',
    'icon' => 'fa-solid fa-sliders',
    'route' => 'soho.platform.attribute',
    'permission' => 'soho.product.attribute.view',
    'roles' => 
    array (
      0 => 'soho.platform_operator',
    ),
    'order' => 216,
    'source' => 'platform.attribute',
    'module' => 'soho',
    'sort' => 216,
    'children' => 
    array (
    ),
  ),
  153 => 
  array (
    'key' => 'soho.sku_management',
    'group' => '经营分析',
    'label' => 'SKU管理',
    'icon' => 'fa-solid fa-cubes',
    'route' => 'soho.product.sku',
    'permission' => 'soho.sku.view',
    'roles' => 
    array (
      0 => 'soho.product_admin',
      1 => 'soho.product_reviewer',
    ),
    'order' => 217,
    'source' => 'product.sku',
    'module' => 'soho',
    'sort' => 217,
    'children' => 
    array (
    ),
  ),
  154 => 
  array (
    'key' => 'soho.product_version',
    'group' => '经营分析',
    'label' => '商品版本',
    'icon' => 'fa-solid fa-code-fork',
    'route' => 'soho.product.versions',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_admin',
      1 => 'soho.product_reviewer',
    ),
    'order' => 218,
    'source' => 'product.versions',
    'module' => 'soho',
    'sort' => 218,
    'children' => 
    array (
    ),
  ),
  155 => 
  array (
    'key' => 'soho.product_import',
    'group' => '经营分析',
    'label' => '商品导入',
    'icon' => 'fa-solid fa-upload',
    'route' => 'soho.product.import',
    'permission' => 'soho.product.create',
    'roles' => 
    array (
      0 => 'soho.product_admin',
    ),
    'order' => 219,
    'source' => 'product.import',
    'module' => 'soho',
    'sort' => 219,
    'children' => 
    array (
    ),
  ),
  156 => 
  array (
    'key' => 'soho.product_task_pending_edit',
    'group' => '经营分析',
    'label' => '待修改商品',
    'icon' => 'fa-solid fa-pencil',
    'route' => 'soho.product.tasks.pending-edit',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_admin',
    ),
    'order' => 220,
    'source' => 'product.tasks.pending-edit',
    'module' => 'soho',
    'sort' => 220,
    'children' => 
    array (
    ),
  ),
  157 => 
  array (
    'key' => 'soho.product_task_returned',
    'group' => '经营分析',
    'label' => '退回商品',
    'icon' => 'fa-solid fa-undo',
    'route' => 'soho.product.tasks.returned',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_admin',
    ),
    'order' => 221,
    'source' => 'product.tasks.returned',
    'module' => 'soho',
    'sort' => 221,
    'children' => 
    array (
    ),
  ),
  158 => 
  array (
    'key' => 'soho.product_task_exception',
    'group' => '经营分析',
    'label' => '商品资料异常',
    'icon' => 'fa-solid fa-exclamation',
    'route' => 'soho.product.tasks.exception',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_admin',
    ),
    'order' => 222,
    'source' => 'product.tasks.exception',
    'module' => 'soho',
    'sort' => 222,
    'children' => 
    array (
    ),
  ),
  159 => 
  array (
    'key' => 'soho.product_review_pending',
    'group' => '经营分析',
    'label' => '待审核商品',
    'icon' => 'fa-solid fa-hourglass',
    'route' => 'soho.product.review.pending',
    'permission' => 'soho.product.review',
    'roles' => 
    array (
      0 => 'soho.product_reviewer',
    ),
    'order' => 223,
    'source' => 'product.review.pending',
    'module' => 'soho',
    'sort' => 223,
    'children' => 
    array (
    ),
  ),
  160 => 
  array (
    'key' => 'soho.product_review_record',
    'group' => '经营分析',
    'label' => '商品审核记录',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.product.review.record',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_reviewer',
    ),
    'order' => 224,
    'source' => 'product.review.record',
    'module' => 'soho',
    'sort' => 224,
    'children' => 
    array (
    ),
  ),
  161 => 
  array (
    'key' => 'soho.product_change_review',
    'group' => '经营分析',
    'label' => '商品变更审核',
    'icon' => 'fa-solid fa-edit',
    'route' => 'soho.product.review.change',
    'permission' => 'soho.product.review',
    'roles' => 
    array (
      0 => 'soho.product_reviewer',
    ),
    'order' => 225,
    'source' => 'product.review.change',
    'module' => 'soho',
    'sort' => 225,
    'children' => 
    array (
    ),
  ),
  162 => 
  array (
    'key' => 'soho.product_spu_detail',
    'group' => '经营分析',
    'label' => 'SPU详情',
    'icon' => 'fa-solid fa-info-circle',
    'route' => 'soho.product.spu.detail',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.product_reviewer',
    ),
    'order' => 226,
    'source' => 'product.spu.detail',
    'module' => 'soho',
    'sort' => 226,
    'children' => 
    array (
    ),
  ),
  163 => 
  array (
    'key' => 'soho.order_pending_fulfill',
    'group' => '经营分析',
    'label' => '待履约',
    'icon' => 'fa-solid fa-hourglass-half',
    'route' => 'soho.order.pending-fulfill',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 227,
    'source' => 'order.pending-fulfill',
    'module' => 'soho',
    'sort' => 227,
    'children' => 
    array (
    ),
  ),
  164 => 
  array (
    'key' => 'soho.order_split',
    'group' => '经营分析',
    'label' => '订单拆分',
    'icon' => 'fa-solid fa-code-fork',
    'route' => 'soho.order.split',
    'permission' => 'soho.order.edit',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 228,
    'source' => 'order.split',
    'module' => 'soho',
    'sort' => 228,
    'children' => 
    array (
    ),
  ),
  165 => 
  array (
    'key' => 'soho.order_merge',
    'group' => '经营分析',
    'label' => '订单合并',
    'icon' => 'fa-solid fa-code-merge',
    'route' => 'soho.order.merge',
    'permission' => 'soho.order.edit',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 229,
    'source' => 'order.merge',
    'module' => 'soho',
    'sort' => 229,
    'children' => 
    array (
    ),
  ),
  166 => 
  array (
    'key' => 'soho.order_export',
    'group' => '经营分析',
    'label' => '订单导出',
    'icon' => 'fa-solid fa-download',
    'route' => 'soho.order.export',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 230,
    'source' => 'order.export',
    'module' => 'soho',
    'sort' => 230,
    'children' => 
    array (
    ),
  ),
  167 => 
  array (
    'key' => 'soho.warehouse_assignment',
    'group' => '经营分析',
    'label' => '分仓管理',
    'icon' => 'fa-solid fa-building',
    'route' => 'soho.order.warehouse.assign',
    'permission' => 'soho.order.edit',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 231,
    'source' => 'order.warehouse.assign',
    'module' => 'soho',
    'sort' => 231,
    'children' => 
    array (
    ),
  ),
  168 => 
  array (
    'key' => 'soho.fulfillment_order',
    'group' => '经营分析',
    'label' => '履约单',
    'icon' => 'fa-solid fa-file-text',
    'route' => 'soho.order.fulfillment',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 232,
    'source' => 'order.fulfillment',
    'module' => 'soho',
    'sort' => 232,
    'children' => 
    array (
    ),
  ),
  169 => 
  array (
    'key' => 'soho.order_stock_shortage',
    'group' => '经营分析',
    'label' => '缺货订单',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.order.stock-shortage',
    'permission' => 'soho.order.view',
    'roles' => 
    array (
      0 => 'soho.order_manager',
    ),
    'order' => 233,
    'source' => 'order.stock-shortage',
    'module' => 'soho',
    'sort' => 233,
    'children' => 
    array (
    ),
  ),
  170 => 
  array (
    'key' => 'soho.customer_tag',
    'group' => '经营分析',
    'label' => '客户标签',
    'icon' => 'fa-solid fa-tags',
    'route' => 'soho.customer.tags',
    'permission' => 'soho.customer.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 234,
    'source' => 'customer.tags',
    'module' => 'soho',
    'sort' => 234,
    'children' => 
    array (
    ),
  ),
  171 => 
  array (
    'key' => 'soho.customer_privacy_request',
    'group' => '经营分析',
    'label' => '客户隐私请求',
    'icon' => 'fa-solid fa-user-secret',
    'route' => 'soho.customer.privacy',
    'permission' => 'soho.customer.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 235,
    'source' => 'customer.privacy',
    'module' => 'soho',
    'sort' => 235,
    'children' => 
    array (
    ),
  ),
  172 => 
  array (
    'key' => 'soho.aftersale_apply',
    'group' => '经营分析',
    'label' => '售后申请',
    'icon' => 'fa-solid fa-life-ring',
    'route' => 'soho.aftersale.apply',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 236,
    'source' => 'aftersale.apply',
    'module' => 'soho',
    'sort' => 236,
    'children' => 
    array (
    ),
  ),
  173 => 
  array (
    'key' => 'soho.aftersale_supplement',
    'group' => '经营分析',
    'label' => '待补充资料',
    'icon' => 'fa-solid fa-paperclip',
    'route' => 'soho.aftersale.supplement',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 237,
    'source' => 'aftersale.supplement',
    'module' => 'soho',
    'sort' => 237,
    'children' => 
    array (
    ),
  ),
  174 => 
  array (
    'key' => 'soho.aftersale_progress',
    'group' => '经营分析',
    'label' => '售后进度',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'soho.aftersale.progress',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 238,
    'source' => 'aftersale.progress',
    'module' => 'soho',
    'sort' => 238,
    'children' => 
    array (
    ),
  ),
  175 => 
  array (
    'key' => 'soho.aftersale_communication',
    'group' => '经营分析',
    'label' => '售后沟通记录',
    'icon' => 'fa-solid fa-comments',
    'route' => 'soho.aftersale.communication',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.customer_service',
    ),
    'order' => 239,
    'source' => 'aftersale.communication',
    'module' => 'soho',
    'sort' => 239,
    'children' => 
    array (
    ),
  ),
  176 => 
  array (
    'key' => 'soho.warehouse_zone',
    'group' => '经营分析',
    'label' => '库区管理',
    'icon' => 'fa-solid fa-th-large',
    'route' => 'soho.warehouse.zones',
    'permission' => 'soho.warehouse.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 240,
    'source' => 'warehouse.zones',
    'module' => 'soho',
    'sort' => 240,
    'children' => 
    array (
    ),
  ),
  177 => 
  array (
    'key' => 'soho.warehouse_location',
    'group' => '经营分析',
    'label' => '库位管理',
    'icon' => 'fa-solid fa-map',
    'route' => 'soho.warehouse.locations',
    'permission' => 'soho.warehouse.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 241,
    'source' => 'warehouse.locations',
    'module' => 'soho',
    'sort' => 241,
    'children' => 
    array (
    ),
  ),
  178 => 
  array (
    'key' => 'soho.warehouse_stock_warning',
    'group' => '经营分析',
    'label' => '库存预警',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.warehouse.stock-warning',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 242,
    'source' => 'warehouse.stock-warning',
    'module' => 'soho',
    'sort' => 242,
    'children' => 
    array (
    ),
  ),
  179 => 
  array (
    'key' => 'soho.warehouse_inbound_order',
    'group' => '经营分析',
    'label' => '入库单',
    'icon' => 'fa-solid fa-arrow-down',
    'route' => 'soho.warehouse.inbound',
    'permission' => 'soho.inventory.adjust',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 243,
    'source' => 'warehouse.inbound',
    'module' => 'soho',
    'sort' => 243,
    'children' => 
    array (
    ),
  ),
  180 => 
  array (
    'key' => 'soho.warehouse_outbound_order',
    'group' => '经营分析',
    'label' => '出库单',
    'icon' => 'fa-solid fa-arrow-up',
    'route' => 'soho.warehouse.outbound',
    'permission' => 'soho.inventory.adjust',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 244,
    'source' => 'warehouse.outbound',
    'module' => 'soho',
    'sort' => 244,
    'children' => 
    array (
    ),
  ),
  181 => 
  array (
    'key' => 'soho.warehouse_stocktake_order',
    'group' => '经营分析',
    'label' => '盘点单',
    'icon' => 'fa-solid fa-clipboard',
    'route' => 'soho.warehouse.stocktake',
    'permission' => 'soho.inventory.stocktake',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 245,
    'source' => 'warehouse.stocktake',
    'module' => 'soho',
    'sort' => 245,
    'children' => 
    array (
    ),
  ),
  182 => 
  array (
    'key' => 'soho.warehouse_stock_adjust',
    'group' => '经营分析',
    'label' => '库存调整申请',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.warehouse.stock-adjust',
    'permission' => 'soho.inventory.adjust',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 246,
    'source' => 'warehouse.stock-adjust',
    'module' => 'soho',
    'sort' => 246,
    'children' => 
    array (
    ),
  ),
  183 => 
  array (
    'key' => 'soho.warehouse_inventory_doc',
    'group' => '经营分析',
    'label' => '库存单据',
    'icon' => 'fa-solid fa-file',
    'route' => 'soho.warehouse.inventory-doc',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 247,
    'source' => 'warehouse.inventory-doc',
    'module' => 'soho',
    'sort' => 247,
    'children' => 
    array (
    ),
  ),
  184 => 
  array (
    'key' => 'soho.warehouse_return_receive',
    'group' => '经营分析',
    'label' => '待收货退货',
    'icon' => 'fa-solid fa-inbox',
    'route' => 'soho.warehouse.return.receive',
    'permission' => 'soho.return.receive',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 248,
    'source' => 'warehouse.return.receive',
    'module' => 'soho',
    'sort' => 248,
    'children' => 
    array (
    ),
  ),
  185 => 
  array (
    'key' => 'soho.warehouse_return_inspect',
    'group' => '经营分析',
    'label' => '待质检退货',
    'icon' => 'fa-solid fa-search',
    'route' => 'soho.warehouse.return.inspect',
    'permission' => 'soho.return.inspect',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
    ),
    'order' => 249,
    'source' => 'warehouse.return.inspect',
    'module' => 'soho',
    'sort' => 249,
    'children' => 
    array (
    ),
  ),
  186 => 
  array (
    'key' => 'soho.warehouse_inventory_turnover',
    'group' => '经营分析',
    'label' => '库存周转',
    'icon' => 'fa-solid fa-sync',
    'route' => 'soho.warehouse.inventory.turnover',
    'permission' => 'soho.report.stock',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
      1 => 'soho.data_analyst',
    ),
    'order' => 250,
    'source' => 'warehouse.inventory.turnover',
    'module' => 'soho',
    'sort' => 250,
    'children' => 
    array (
    ),
  ),
  187 => 
  array (
    'key' => 'soho.warehouse_stock_age',
    'group' => '经营分析',
    'label' => '库龄分析',
    'icon' => 'fa-solid fa-hourglass',
    'route' => 'soho.warehouse.stock.age',
    'permission' => 'soho.report.stock',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
      1 => 'soho.data_analyst',
    ),
    'order' => 251,
    'source' => 'warehouse.stock.age',
    'module' => 'soho',
    'sort' => 251,
    'children' => 
    array (
    ),
  ),
  188 => 
  array (
    'key' => 'soho.fulfillment_assignment_pending',
    'group' => '经营分析',
    'label' => '待分配作业',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.fulfillment.assignments.index',
    'permission' => 'soho.fulfillment.view',
    'roles' => 
    array (
      0 => 'soho.warehouse_manager',
      1 => 'soho.order_manager',
    ),
    'order' => 252,
    'source' => 'fulfillment.assignments.index',
    'module' => 'soho',
    'sort' => 252,
    'children' => 
    array (
    ),
  ),
  189 => 
  array (
    'key' => 'soho.pick_task_progress',
    'group' => '经营分析',
    'label' => '执行中任务',
    'icon' => 'fa-solid fa-play-circle',
    'route' => 'soho.pick.tasks.progress',
    'permission' => 'soho.picking.view',
    'roles' => 
    array (
      0 => 'soho.picker',
    ),
    'order' => 253,
    'source' => 'pick.tasks.progress',
    'module' => 'soho',
    'sort' => 253,
    'children' => 
    array (
    ),
  ),
  190 => 
  array (
    'key' => 'soho.pick_task_completed',
    'group' => '经营分析',
    'label' => '已完成任务',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.pick.tasks.completed',
    'permission' => 'soho.picking.view',
    'roles' => 
    array (
      0 => 'soho.picker',
    ),
    'order' => 254,
    'source' => 'pick.tasks.completed',
    'module' => 'soho',
    'sort' => 254,
    'children' => 
    array (
    ),
  ),
  191 => 
  array (
    'key' => 'soho.check_exception',
    'group' => '经营分析',
    'label' => '复核异常',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.check.tasks.exception',
    'permission' => 'soho.checking.view',
    'roles' => 
    array (
      0 => 'soho.checker',
    ),
    'order' => 255,
    'source' => 'check.tasks.exception',
    'module' => 'soho',
    'sort' => 255,
    'children' => 
    array (
    ),
  ),
  192 => 
  array (
    'key' => 'soho.check_completed',
    'group' => '经营分析',
    'label' => '已完成复核',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.check.tasks.completed',
    'permission' => 'soho.checking.view',
    'roles' => 
    array (
      0 => 'soho.checker',
    ),
    'order' => 256,
    'source' => 'check.tasks.completed',
    'module' => 'soho',
    'sort' => 256,
    'children' => 
    array (
    ),
  ),
  193 => 
  array (
    'key' => 'soho.check_record',
    'group' => '经营分析',
    'label' => '复核记录',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.check.tasks.record',
    'permission' => 'soho.checking.view',
    'roles' => 
    array (
      0 => 'soho.checker',
    ),
    'order' => 257,
    'source' => 'check.tasks.record',
    'module' => 'soho',
    'sort' => 257,
    'children' => 
    array (
    ),
  ),
  194 => 
  array (
    'key' => 'soho.pack_material_select',
    'group' => '经营分析',
    'label' => '包材选择',
    'icon' => 'fa-solid fa-cubes',
    'route' => 'soho.pack.tasks.material',
    'permission' => 'soho.packing.execute',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 258,
    'source' => 'pack.tasks.material',
    'module' => 'soho',
    'sort' => 258,
    'children' => 
    array (
    ),
  ),
  195 => 
  array (
    'key' => 'soho.pack_weight_input',
    'group' => '经营分析',
    'label' => '称重录入',
    'icon' => 'fa-solid fa-balance-scale',
    'route' => 'soho.pack.tasks.weight',
    'permission' => 'soho.packing.execute',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 259,
    'source' => 'pack.tasks.weight',
    'module' => 'soho',
    'sort' => 259,
    'children' => 
    array (
    ),
  ),
  196 => 
  array (
    'key' => 'soho.pack_parcel_generate',
    'group' => '经营分析',
    'label' => '包裹生成',
    'icon' => 'fa-solid fa-box',
    'route' => 'soho.pack.tasks.parcel',
    'permission' => 'soho.packing.execute',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 260,
    'source' => 'pack.tasks.parcel',
    'module' => 'soho',
    'sort' => 260,
    'children' => 
    array (
    ),
  ),
  197 => 
  array (
    'key' => 'soho.pack_exception',
    'group' => '经营分析',
    'label' => '打包异常',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.pack.tasks.exception',
    'permission' => 'soho.packing.view',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 261,
    'source' => 'pack.tasks.exception',
    'module' => 'soho',
    'sort' => 261,
    'children' => 
    array (
    ),
  ),
  198 => 
  array (
    'key' => 'soho.pack_completed',
    'group' => '经营分析',
    'label' => '已完成打包',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.pack.tasks.completed',
    'permission' => 'soho.packing.view',
    'roles' => 
    array (
      0 => 'soho.packer',
    ),
    'order' => 262,
    'source' => 'pack.tasks.completed',
    'module' => 'soho',
    'sort' => 262,
    'children' => 
    array (
    ),
  ),
  199 => 
  array (
    'key' => 'soho.logistics_bill',
    'group' => '经营分析',
    'label' => '物流账单',
    'icon' => 'fa-solid fa-file-text',
    'route' => 'soho.logistics.bill',
    'permission' => 'soho.logistics.bill.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 263,
    'source' => 'logistics.bill',
    'module' => 'soho',
    'sort' => 263,
    'children' => 
    array (
    ),
  ),
  200 => 
  array (
    'key' => 'soho.logistics_ship_callback',
    'group' => '经营分析',
    'label' => '发货回传',
    'icon' => 'fa-solid fa-paper-plane',
    'route' => 'soho.logistics.ship.callback',
    'permission' => 'soho.logistics.callback',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 264,
    'source' => 'logistics.ship.callback',
    'module' => 'soho',
    'sort' => 264,
    'children' => 
    array (
    ),
  ),
  201 => 
  array (
    'key' => 'soho.logistics_batch_callback',
    'group' => '经营分析',
    'label' => '批量回传',
    'icon' => 'fa-solid fa-upload',
    'route' => 'soho.logistics.batch.callback',
    'permission' => 'soho.logistics.callback',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 265,
    'source' => 'logistics.batch.callback',
    'module' => 'soho',
    'sort' => 265,
    'children' => 
    array (
    ),
  ),
  202 => 
  array (
    'key' => 'soho.logistics_on_time',
    'group' => '经营分析',
    'label' => '发货及时率',
    'icon' => 'fa-solid fa-clock',
    'route' => 'soho.logistics.on-time',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 266,
    'source' => 'logistics.on-time',
    'module' => 'soho',
    'sort' => 266,
    'children' => 
    array (
    ),
  ),
  203 => 
  array (
    'key' => 'soho.logistics_lead_time',
    'group' => '经营分析',
    'label' => '物流时效',
    'icon' => 'fa-solid fa-hourglass-half',
    'route' => 'soho.logistics.lead-time',
    'permission' => 'soho.logistics.view',
    'roles' => 
    array (
      0 => 'soho.logistics_specialist',
    ),
    'order' => 267,
    'source' => 'logistics.lead-time',
    'module' => 'soho',
    'sort' => 267,
    'children' => 
    array (
    ),
  ),
  204 => 
  array (
    'key' => 'soho.aftersale_exchange_resend',
    'group' => '经营分析',
    'label' => '换货与补发',
    'icon' => 'fa-solid fa-exchange',
    'route' => 'soho.aftersale.exchange',
    'permission' => 'soho.after_sale.handle',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 268,
    'source' => 'aftersale.exchange',
    'module' => 'soho',
    'sort' => 268,
    'children' => 
    array (
    ),
  ),
  205 => 
  array (
    'key' => 'soho.aftersale_dispute',
    'group' => '经营分析',
    'label' => '售后争议',
    'icon' => 'fa-solid fa-gavel',
    'route' => 'soho.aftersale.dispute',
    'permission' => 'soho.after_sale.handle',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 269,
    'source' => 'aftersale.dispute',
    'module' => 'soho',
    'sort' => 269,
    'children' => 
    array (
    ),
  ),
  206 => 
  array (
    'key' => 'soho.aftersale_operation_log',
    'group' => '经营分析',
    'label' => '售后操作记录',
    'icon' => 'fa-solid fa-history',
    'route' => 'soho.aftersale.operation.log',
    'permission' => 'soho.after_sale.view',
    'roles' => 
    array (
      0 => 'soho.aftersale_manager',
    ),
    'order' => 270,
    'source' => 'aftersale.operation.log',
    'module' => 'soho',
    'sort' => 270,
    'children' => 
    array (
    ),
  ),
  207 => 
  array (
    'key' => 'soho.finance_refund_flow',
    'group' => '经营分析',
    'label' => '退款流水',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.finance.refund.flow',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 271,
    'source' => 'finance.refund.flow',
    'module' => 'soho',
    'sort' => 271,
    'children' => 
    array (
    ),
  ),
  208 => 
  array (
    'key' => 'soho.finance_platform_settlement',
    'group' => '经营分析',
    'label' => '平台结算',
    'icon' => 'fa-solid fa-money',
    'route' => 'soho.finance.platform.settlement',
    'permission' => 'soho.finance.settle',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 272,
    'source' => 'finance.platform.settlement',
    'module' => 'soho',
    'sort' => 272,
    'children' => 
    array (
    ),
  ),
  209 => 
  array (
    'key' => 'soho.finance_settlement_detail',
    'group' => '经营分析',
    'label' => '结算明细',
    'icon' => 'fa-solid fa-list-alt',
    'route' => 'soho.finance.settlement.detail',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 273,
    'source' => 'finance.settlement.detail',
    'module' => 'soho',
    'sort' => 273,
    'children' => 
    array (
    ),
  ),
  210 => 
  array (
    'key' => 'soho.finance_cost_allocation',
    'group' => '经营分析',
    'label' => '费用分摊',
    'icon' => 'fa-solid fa-percent',
    'route' => 'soho.finance.cost.allocation',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 274,
    'source' => 'finance.cost.allocation',
    'module' => 'soho',
    'sort' => 274,
    'children' => 
    array (
    ),
  ),
  211 => 
  array (
    'key' => 'soho.finance_reconcile_task',
    'group' => '经营分析',
    'label' => '对账任务',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'soho.finance.reconcile.task',
    'permission' => 'soho.finance.reconcile',
    'roles' => 
    array (
      0 => 'soho.finance_staff',
      1 => 'soho.finance_manager',
    ),
    'order' => 275,
    'source' => 'finance.reconcile.task',
    'module' => 'soho',
    'sort' => 275,
    'children' => 
    array (
    ),
  ),
  212 => 
  array (
    'key' => 'soho.finance_settlement_audit',
    'group' => '经营分析',
    'label' => '结算审核',
    'icon' => 'fa-solid fa-check-circle',
    'route' => 'soho.finance.settlement.audit',
    'permission' => 'soho.finance.settle',
    'roles' => 
    array (
      0 => 'soho.finance_manager',
    ),
    'order' => 276,
    'source' => 'finance.settlement.audit',
    'module' => 'soho',
    'sort' => 276,
    'children' => 
    array (
    ),
  ),
  213 => 
  array (
    'key' => 'soho.finance_cost_audit',
    'group' => '经营分析',
    'label' => '费用分摊审核',
    'icon' => 'fa-solid fa-check',
    'route' => 'soho.finance.cost.audit',
    'permission' => 'soho.finance.view',
    'roles' => 
    array (
      0 => 'soho.finance_manager',
    ),
    'order' => 277,
    'source' => 'finance.cost.audit',
    'module' => 'soho',
    'sort' => 277,
    'children' => 
    array (
    ),
  ),
  214 => 
  array (
    'key' => 'soho.finance_big_diff_approval',
    'group' => '经营分析',
    'label' => '大额差异审批',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.finance.diff.approval',
    'permission' => 'soho.finance.reconcile',
    'roles' => 
    array (
      0 => 'soho.finance_manager',
    ),
    'order' => 278,
    'source' => 'finance.diff.approval',
    'module' => 'soho',
    'sort' => 278,
    'children' => 
    array (
    ),
  ),
  215 => 
  array (
    'key' => 'soho.procurement_order_detail',
    'group' => '经营分析',
    'label' => '采购订单明细',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.procurement.orders.index',
    'permission' => 'soho.procurement.order.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 279,
    'source' => 'procurement.orders.index',
    'module' => 'soho',
    'sort' => 279,
    'children' => 
    array (
    ),
  ),
  216 => 
  array (
    'key' => 'soho.procurement_safe_stock',
    'group' => '经营分析',
    'label' => '安全库存',
    'icon' => 'fa-solid fa-shield',
    'route' => 'soho.procurement.safe-stocks.index',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 280,
    'source' => 'procurement.safe-stocks.index',
    'module' => 'soho',
    'sort' => 280,
    'children' => 
    array (
    ),
  ),
  217 => 
  array (
    'key' => 'soho.procurement_in_transit',
    'group' => '经营分析',
    'label' => '在途库存',
    'icon' => 'fa-solid fa-truck',
    'route' => 'soho.procurement.arrivals.index',
    'permission' => 'soho.procurement.arrival',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 281,
    'source' => 'procurement.arrivals.index',
    'module' => 'soho',
    'sort' => 281,
    'children' => 
    array (
    ),
  ),
  218 => 
  array (
    'key' => 'soho.procurement_approval',
    'group' => '经营分析',
    'label' => '采购审批',
    'icon' => 'fa-solid fa-check',
    'route' => 'soho.procurement.approvals.index',
    'permission' => 'soho.procurement.order.approve',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 282,
    'source' => 'procurement.approvals.index',
    'module' => 'soho',
    'sort' => 282,
    'children' => 
    array (
    ),
  ),
  219 => 
  array (
    'key' => 'soho.procurement_supplier_eval',
    'group' => '经营分析',
    'label' => '供应商评估',
    'icon' => 'fa-solid fa-star',
    'route' => 'soho.procurement.suppliers.index',
    'permission' => 'soho.supplier.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 283,
    'source' => 'procurement.suppliers.index',
    'module' => 'soho',
    'sort' => 283,
    'children' => 
    array (
    ),
  ),
  220 => 
  array (
    'key' => 'soho.procurement_safe_stock_config',
    'group' => '经营分析',
    'label' => '安全库存配置',
    'icon' => 'fa-solid fa-cog',
    'route' => 'soho.procurement.safe-stocks.index',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.procurement_staff',
      1 => 'soho.procurement_manager',
    ),
    'order' => 284,
    'source' => 'procurement.safe-stocks.index',
    'module' => 'soho',
    'sort' => 284,
    'children' => 
    array (
    ),
  ),
  221 => 
  array (
    'key' => 'soho.report_order',
    'group' => '经营分析',
    'label' => '订单报表',
    'icon' => 'fa-solid fa-shopping-cart',
    'route' => 'soho.report.order',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.data_analyst',
    ),
    'order' => 285,
    'source' => 'report.order',
    'module' => 'soho',
    'sort' => 285,
    'children' => 
    array (
    ),
  ),
  222 => 
  array (
    'key' => 'soho.report_aftersale',
    'group' => '经营分析',
    'label' => '售后报表',
    'icon' => 'fa-solid fa-life-ring',
    'route' => 'soho.report.aftersale',
    'permission' => 'soho.report.aftersale',
    'roles' => 
    array (
      0 => 'soho.operation_manager',
      1 => 'soho.data_analyst',
    ),
    'order' => 286,
    'source' => 'report.aftersale',
    'module' => 'soho',
    'sort' => 286,
    'children' => 
    array (
    ),
  ),
  223 => 
  array (
    'key' => 'soho.report_shop_analysis',
    'group' => '经营分析',
    'label' => '店铺分析',
    'icon' => 'fa-solid fa-store',
    'route' => 'soho.report.shop.analysis',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.data_analyst',
    ),
    'order' => 287,
    'source' => 'report.shop.analysis',
    'module' => 'soho',
    'sort' => 287,
    'children' => 
    array (
    ),
  ),
  224 => 
  array (
    'key' => 'soho.report_sku_analysis',
    'group' => '经营分析',
    'label' => 'SKU分析',
    'icon' => 'fa-solid fa-cubes',
    'route' => 'soho.report.sku.analysis',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.data_analyst',
    ),
    'order' => 288,
    'source' => 'report.sku.analysis',
    'module' => 'soho',
    'sort' => 288,
    'children' => 
    array (
    ),
  ),
  225 => 
  array (
    'key' => 'soho.report_inventory_turnover',
    'group' => '经营分析',
    'label' => '库存周转',
    'icon' => 'fa-solid fa-sync',
    'route' => 'soho.report.inventory.turnover',
    'permission' => 'soho.report.stock',
    'roles' => 
    array (
      0 => 'soho.data_analyst',
    ),
    'order' => 289,
    'source' => 'report.inventory.turnover',
    'module' => 'soho',
    'sort' => 289,
    'children' => 
    array (
    ),
  ),
  226 => 
  array (
    'key' => 'soho.report_metrics_definition',
    'group' => '经营分析',
    'label' => '指标口径',
    'icon' => 'fa-solid fa-calculator',
    'route' => 'soho.report.metrics.definition',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.data_analyst',
    ),
    'order' => 290,
    'source' => 'report.metrics.definition',
    'module' => 'soho',
    'sort' => 290,
    'children' => 
    array (
    ),
  ),
  227 => 
  array (
    'key' => 'soho.readonly_order_report',
    'group' => '经营分析',
    'label' => '订单报表',
    'icon' => 'fa-solid fa-shopping-cart',
    'route' => 'soho.readonly.order.report',
    'permission' => 'soho.report.sales',
    'roles' => 
    array (
      0 => 'soho.read_only',
    ),
    'order' => 291,
    'source' => 'readonly.order.report',
    'module' => 'soho',
    'sort' => 291,
    'children' => 
    array (
    ),
  ),
  228 => 
  array (
    'key' => 'soho.readonly_listing_view',
    'group' => '经营分析',
    'label' => 'Listing查看',
    'icon' => 'fa-solid fa-list',
    'route' => 'soho.readonly.listing.view',
    'permission' => 'soho.product.view',
    'roles' => 
    array (
      0 => 'soho.read_only',
    ),
    'order' => 292,
    'source' => 'readonly.listing.view',
    'module' => 'soho',
    'sort' => 292,
    'children' => 
    array (
    ),
  ),
  229 => 
  array (
    'key' => 'soho.readonly_inventory_warning',
    'group' => '经营分析',
    'label' => '库存预警',
    'icon' => 'fa-solid fa-exclamation-triangle',
    'route' => 'soho.readonly.inventory.warning',
    'permission' => 'soho.inventory.view',
    'roles' => 
    array (
      0 => 'soho.read_only',
    ),
    'order' => 293,
    'source' => 'readonly.inventory.warning',
    'module' => 'soho',
    'sort' => 293,
    'children' => 
    array (
    ),
  ),
  230 => 
  array (
    'key' => 'soho.marketing_campaign_join',
    'group' => '经营分析',
    'label' => '活动报名',
    'icon' => 'fa-solid fa-sign-in',
    'route' => 'soho.marketing.campaign.join',
    'permission' => 'soho.promotion.edit',
    'roles' => 
    array (
      0 => 'soho.marketing_staff',
    ),
    'order' => 294,
    'source' => 'marketing.campaign.join',
    'module' => 'soho',
    'sort' => 294,
    'children' => 
    array (
    ),
  ),
  231 => 
  array (
    'key' => 'soho.marketing_campaign_rule',
    'group' => '经营分析',
    'label' => '活动规则',
    'icon' => 'fa-solid fa-list-alt',
    'route' => 'soho.marketing.campaign.rule',
    'permission' => 'soho.promotion.edit',
    'roles' => 
    array (
      0 => 'soho.marketing_staff',
    ),
    'order' => 295,
    'source' => 'marketing.campaign.rule',
    'module' => 'soho',
    'sort' => 295,
    'children' => 
    array (
    ),
  ),
);
