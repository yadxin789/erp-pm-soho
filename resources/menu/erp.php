<?php

/*
| ERP 模块菜单（TC-08 由 docs/menu/baseline-menu.json 生成，勿手改）
| key / group / label / icon / route / permission / roles / order / source
| route 为合并后真实路由名；permission 已对照 RBAC 库校验；roles 为模块内角色白名单（null=不限）
*/

return array (
  0 => 
  array (
    'key' => 'erp.app.dashboard',
    'group' => '业务概览',
    'label' => '仪表盘',
    'icon' => 'fa-solid fa-gauge-high',
    'route' => 'erp.dashboard',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 1,
    'source' => 'app.dashboard',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 1,
    'children' => 
    array (
    ),
  ),
  1 => 
  array (
    'key' => 'erp.app.ai.chat',
    'group' => '智能助手',
    'label' => 'AI聊天',
    'icon' => 'fa-solid fa-robot',
    'route' => 'erp.ai.chat',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 2,
    'source' => 'app.ai.chat',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 2,
    'children' => 
    array (
    ),
  ),
  2 => 
  array (
    'key' => 'erp.app.ai.analysis-center',
    'group' => '智能助手',
    'label' => '智能分析中心',
    'icon' => 'fa-solid fa-wand-magic-sparkles',
    'route' => 'erp.ai.analysis-center',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 3,
    'source' => 'app.ai.analysis-center',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 3,
    'children' => 
    array (
    ),
  ),
  3 => 
  array (
    'key' => 'erp.app.chat',
    'group' => '协作沟通',
    'label' => '群聊',
    'icon' => 'fa-solid fa-comments',
    'route' => 'erp.chat',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 4,
    'source' => 'app.chat',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 4,
    'children' => 
    array (
    ),
  ),
  4 => 
  array (
    'key' => 'erp.app.profile.edit',
    'group' => '协作沟通',
    'label' => '个人资料',
    'icon' => 'fa-solid fa-id-card',
    'route' => 'erp.profile.edit',
    'permission' => NULL,
    'roles' => NULL,
    'order' => 5,
    'source' => 'app.profile.edit',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 5,
    'children' => 
    array (
    ),
  ),
  5 => 
  array (
    'key' => 'erp.app.sales.orders.index',
    'group' => '销售管理',
    'label' => '销售订单',
    'icon' => 'fa-solid fa-file-invoice',
    'route' => 'erp.sales.orders.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 6,
    'source' => 'app.sales.orders.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 6,
    'children' => 
    array (
    ),
  ),
  6 => 
  array (
    'key' => 'erp.app.sales.quotations.index',
    'group' => '销售管理',
    'label' => '报价单',
    'icon' => 'fa-solid fa-file-lines',
    'route' => 'erp.sales.quotations.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 7,
    'source' => 'app.sales.quotations.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 7,
    'children' => 
    array (
    ),
  ),
  7 => 
  array (
    'key' => 'erp.app.sales.shipments.index',
    'group' => '销售管理',
    'label' => '发货管理',
    'icon' => 'fa-solid fa-truck',
    'route' => 'erp.sales.shipments.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 8,
    'source' => 'app.sales.shipments.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 8,
    'children' => 
    array (
    ),
  ),
  8 => 
  array (
    'key' => 'erp.app.sales.returns.index',
    'group' => '销售管理',
    'label' => '销售退货',
    'icon' => 'fa-solid fa-rotate-left',
    'route' => 'erp.sales.returns.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 9,
    'source' => 'app.sales.returns.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 9,
    'children' => 
    array (
    ),
  ),
  9 => 
  array (
    'key' => 'erp.app.purchase.orders.index',
    'group' => '采购管理',
    'label' => '采购订单',
    'icon' => 'fa-solid fa-file-invoice',
    'route' => 'erp.purchase.orders.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.purchase_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 10,
    'source' => 'app.purchase.orders.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 10,
    'children' => 
    array (
    ),
  ),
  10 => 
  array (
    'key' => 'erp.app.purchase.requisitions.index',
    'group' => '采购管理',
    'label' => '请购单',
    'icon' => 'fa-solid fa-clipboard',
    'route' => 'erp.purchase.requisitions.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.purchase_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 11,
    'source' => 'app.purchase.requisitions.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 11,
    'children' => 
    array (
    ),
  ),
  11 => 
  array (
    'key' => 'erp.app.purchase.receipts.index',
    'group' => '采购管理',
    'label' => '收货管理',
    'icon' => 'fa-solid fa-box-open',
    'route' => 'erp.purchase.receipts.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.purchase_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 12,
    'source' => 'app.purchase.receipts.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 12,
    'children' => 
    array (
    ),
  ),
  12 => 
  array (
    'key' => 'erp.app.purchase.returns.index',
    'group' => '采购管理',
    'label' => '采购退货',
    'icon' => 'fa-solid fa-rotate-left',
    'route' => 'erp.purchase.returns.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.purchase_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 13,
    'source' => 'app.purchase.returns.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 13,
    'children' => 
    array (
    ),
  ),
  13 => 
  array (
    'key' => 'erp.app.production.orders.index',
    'group' => '生产管理',
    'label' => '生产工单',
    'icon' => 'fa-solid fa-industry',
    'route' => 'erp.production.orders.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 14,
    'source' => 'app.production.orders.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 14,
    'children' => 
    array (
    ),
  ),
  14 => 
  array (
    'key' => 'erp.app.production.plans.index',
    'group' => '生产管理',
    'label' => '生产计划',
    'icon' => 'fa-solid fa-diagram-project',
    'route' => 'erp.production.plans.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 15,
    'source' => 'app.production.plans.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 15,
    'children' => 
    array (
    ),
  ),
  15 => 
  array (
    'key' => 'erp.app.production.issues.index',
    'group' => '生产管理',
    'label' => '领料管理',
    'icon' => 'fa-solid fa-dolly',
    'route' => 'erp.production.issues.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 16,
    'source' => 'app.production.issues.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 16,
    'children' => 
    array (
    ),
  ),
  16 => 
  array (
    'key' => 'erp.app.production.receipts.index',
    'group' => '生产管理',
    'label' => '成品入库',
    'icon' => 'fa-solid fa-warehouse',
    'route' => 'erp.production.receipts.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.quality_manager',
      3 => 'erp.accountant',
      4 => 'erp.viewer',
    ),
    'order' => 17,
    'source' => 'app.production.receipts.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 17,
    'children' => 
    array (
    ),
  ),
  17 => 
  array (
    'key' => 'erp.app.inventory.index',
    'group' => '库存管理',
    'label' => '库存查询',
    'icon' => 'fa-solid fa-boxes-stacked',
    'route' => 'erp.inventory.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.accountant',
      3 => 'erp.viewer',
    ),
    'order' => 18,
    'source' => 'app.inventory.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 18,
    'children' => 
    array (
    ),
  ),
  18 => 
  array (
    'key' => 'erp.app.inventory.transfer',
    'group' => '库存管理',
    'label' => '库存调拨',
    'icon' => 'fa-solid fa-right-left',
    'route' => 'erp.inventory.transfer',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.accountant',
      3 => 'erp.viewer',
    ),
    'order' => 19,
    'source' => 'app.inventory.transfer',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 19,
    'children' => 
    array (
    ),
  ),
  19 => 
  array (
    'key' => 'erp.app.inventory.trace',
    'group' => '库存管理',
    'label' => '批次追溯',
    'icon' => 'fa-solid fa-code-branch',
    'route' => 'erp.inventory.trace',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.accountant',
      3 => 'erp.viewer',
    ),
    'order' => 20,
    'source' => 'app.inventory.trace',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 20,
    'children' => 
    array (
    ),
  ),
  20 => 
  array (
    'key' => 'erp.app.inventory.logistics',
    'group' => '库存管理',
    'label' => '物流查询',
    'icon' => 'fa-solid fa-truck-fast',
    'route' => 'erp.inventory.logistics',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.production_manager',
      1 => 'erp.warehouse_manager',
      2 => 'erp.accountant',
      3 => 'erp.viewer',
    ),
    'order' => 21,
    'source' => 'app.inventory.logistics',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 21,
    'children' => 
    array (
    ),
  ),
  21 => 
  array (
    'key' => 'erp.app.quality.inspections.index',
    'group' => '质量管理',
    'label' => '检验管理',
    'icon' => 'fa-solid fa-clipboard-check',
    'route' => 'erp.quality.inspections.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.quality_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 22,
    'source' => 'app.quality.inspections.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 22,
    'children' => 
    array (
    ),
  ),
  22 => 
  array (
    'key' => 'erp.app.quality.defects.index',
    'group' => '质量管理',
    'label' => '缺陷管理',
    'icon' => 'fa-solid fa-bug',
    'route' => 'erp.quality.defects.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.quality_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 23,
    'source' => 'app.quality.defects.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 23,
    'children' => 
    array (
    ),
  ),
  23 => 
  array (
    'key' => 'erp.app.quality.reports',
    'group' => '质量管理',
    'label' => '质量报表',
    'icon' => 'fa-solid fa-chart-column',
    'route' => 'erp.quality.reports',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.quality_manager',
      1 => 'erp.accountant',
      2 => 'erp.viewer',
    ),
    'order' => 24,
    'source' => 'app.quality.reports',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 24,
    'children' => 
    array (
    ),
  ),
  24 => 
  array (
    'key' => 'erp.app.finance.invoices',
    'group' => '财务与 CRM',
    'label' => '客户发票',
    'icon' => 'fa-solid fa-file-invoice-dollar',
    'route' => 'erp.finance.invoices',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.accountant',
    ),
    'order' => 25,
    'source' => 'app.finance.invoices',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 25,
    'children' => 
    array (
    ),
  ),
  25 => 
  array (
    'key' => 'erp.app.finance.bills',
    'group' => '财务与 CRM',
    'label' => '供应商账单',
    'icon' => 'fa-solid fa-money-bill',
    'route' => 'erp.finance.bills',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.accountant',
    ),
    'order' => 26,
    'source' => 'app.finance.bills',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 26,
    'children' => 
    array (
    ),
  ),
  26 => 
  array (
    'key' => 'erp.app.finance.payments',
    'group' => '财务与 CRM',
    'label' => '收付款',
    'icon' => 'fa-solid fa-cash-register',
    'route' => 'erp.finance.payments',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.accountant',
    ),
    'order' => 27,
    'source' => 'app.finance.payments',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 27,
    'children' => 
    array (
    ),
  ),
  27 => 
  array (
    'key' => 'erp.app.crm.leads',
    'group' => '财务与 CRM',
    'label' => '销售线索',
    'icon' => 'fa-solid fa-user-plus',
    'route' => 'erp.crm.leads',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
    ),
    'order' => 28,
    'source' => 'app.crm.leads',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 28,
    'children' => 
    array (
    ),
  ),
  28 => 
  array (
    'key' => 'erp.app.crm.pipeline',
    'group' => '财务与 CRM',
    'label' => '销售漏斗',
    'icon' => 'fa-solid fa-filter',
    'route' => 'erp.crm.pipeline',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
    ),
    'order' => 29,
    'source' => 'app.crm.pipeline',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 29,
    'children' => 
    array (
    ),
  ),
  29 => 
  array (
    'key' => 'erp.app.masterdata.items.index',
    'group' => '主数据',
    'label' => '物料信息',
    'icon' => 'fa-solid fa-box',
    'route' => 'erp.masterdata.items.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 30,
    'source' => 'app.masterdata.items.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 30,
    'children' => 
    array (
    ),
  ),
  30 => 
  array (
    'key' => 'erp.app.masterdata.customers.index',
    'group' => '主数据',
    'label' => '客户信息',
    'icon' => 'fa-solid fa-users',
    'route' => 'erp.masterdata.customers.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 31,
    'source' => 'app.masterdata.customers.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 31,
    'children' => 
    array (
    ),
  ),
  31 => 
  array (
    'key' => 'erp.app.masterdata.suppliers.index',
    'group' => '主数据',
    'label' => '供应商信息',
    'icon' => 'fa-solid fa-truck-field',
    'route' => 'erp.masterdata.suppliers.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 32,
    'source' => 'app.masterdata.suppliers.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 32,
    'children' => 
    array (
    ),
  ),
  32 => 
  array (
    'key' => 'erp.app.masterdata.warehouses.index',
    'group' => '主数据',
    'label' => '仓库信息',
    'icon' => 'fa-solid fa-warehouse',
    'route' => 'erp.masterdata.warehouses.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 33,
    'source' => 'app.masterdata.warehouses.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 33,
    'children' => 
    array (
    ),
  ),
  33 => 
  array (
    'key' => 'erp.app.masterdata.boms.index',
    'group' => '主数据',
    'label' => 'BOM清单',
    'icon' => 'fa-solid fa-diagram-project',
    'route' => 'erp.masterdata.boms.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 34,
    'source' => 'app.masterdata.boms.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 34,
    'children' => 
    array (
    ),
  ),
  34 => 
  array (
    'key' => 'erp.app.hr.employees.index',
    'group' => '扩展与报表',
    'label' => '员工档案',
    'icon' => 'fa-solid fa-id-badge',
    'route' => 'erp.hr.employees.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.hr_specialist',
    ),
    'order' => 35,
    'source' => 'app.hr.employees.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 35,
    'children' => 
    array (
    ),
  ),
  35 => 
  array (
    'key' => 'erp.app.hr.payroll.index',
    'group' => '扩展与报表',
    'label' => '薪资工资单',
    'icon' => 'fa-solid fa-money-check-dollar',
    'route' => 'erp.hr.payroll.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.hr_specialist',
    ),
    'order' => 36,
    'source' => 'app.hr.payroll.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 36,
    'children' => 
    array (
    ),
  ),
  36 => 
  array (
    'key' => 'erp.app.hr.performances.index',
    'group' => '扩展与报表',
    'label' => '绩效评估',
    'icon' => 'fa-solid fa-ranking-star',
    'route' => 'erp.hr.performances.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.hr_specialist',
    ),
    'order' => 37,
    'source' => 'app.hr.performances.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 37,
    'children' => 
    array (
    ),
  ),
  37 => 
  array (
    'key' => 'erp.app.hr.recruitments.index',
    'group' => '扩展与报表',
    'label' => '招聘需求',
    'icon' => 'fa-solid fa-bullhorn',
    'route' => 'erp.hr.recruitments.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.hr_specialist',
    ),
    'order' => 38,
    'source' => 'app.hr.recruitments.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 38,
    'children' => 
    array (
    ),
  ),
  38 => 
  array (
    'key' => 'erp.app.hr.applications.index',
    'group' => '扩展与报表',
    'label' => '应聘者',
    'icon' => 'fa-solid fa-users-viewfinder',
    'route' => 'erp.hr.applications.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.hr_specialist',
    ),
    'order' => 39,
    'source' => 'app.hr.applications.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 39,
    'children' => 
    array (
    ),
  ),
  39 => 
  array (
    'key' => 'erp.app.projects.index',
    'group' => '扩展与报表',
    'label' => '项目管理',
    'icon' => 'fa-solid fa-folder-open',
    'route' => 'erp.projects.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'sales_manager',
      1 => 'purchase_manager',
      2 => 'production_manager',
      3 => 'warehouse_manager',
      4 => 'quality_manager',
      5 => 'accountant',
      6 => 'viewer',
      7 => 'hr_specialist',
      8 => 'project_manager',
      9 => 'asset_manager',
      10 => 'ecommerce_operator',
    ),
    'order' => 40,
    'source' => 'app.projects.index',
    'note' => '参与项目校验待 ERP 迁入时接入',
    'module' => 'erp',
    'sort' => 40,
    'children' => 
    array (
    ),
  ),
  40 => 
  array (
    'key' => 'erp.app.tasks.my-tasks',
    'group' => '扩展与报表',
    'label' => '我的任务',
    'icon' => 'fa-solid fa-list-check',
    'route' => 'erp.tasks.my-tasks',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'sales_manager',
      1 => 'purchase_manager',
      2 => 'production_manager',
      3 => 'warehouse_manager',
      4 => 'quality_manager',
      5 => 'accountant',
      6 => 'viewer',
      7 => 'hr_specialist',
      8 => 'project_manager',
      9 => 'asset_manager',
      10 => 'ecommerce_operator',
    ),
    'order' => 41,
    'source' => 'app.tasks.my-tasks',
    'note' => '参与项目校验待 ERP 迁入时接入',
    'module' => 'erp',
    'sort' => 41,
    'children' => 
    array (
    ),
  ),
  41 => 
  array (
    'key' => 'erp.app.tasks.my-audit-tasks',
    'group' => '扩展与报表',
    'label' => '我的审核任务',
    'icon' => 'fa-solid fa-clipboard-check',
    'route' => 'erp.tasks.my-audit-tasks',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'sales_manager',
      1 => 'purchase_manager',
      2 => 'production_manager',
      3 => 'warehouse_manager',
      4 => 'quality_manager',
      5 => 'accountant',
      6 => 'viewer',
      7 => 'hr_specialist',
      8 => 'project_manager',
      9 => 'asset_manager',
      10 => 'ecommerce_operator',
    ),
    'order' => 42,
    'source' => 'app.tasks.my-audit-tasks',
    'note' => '参与项目校验待 ERP 迁入时接入',
    'module' => 'erp',
    'sort' => 42,
    'children' => 
    array (
    ),
  ),
  42 => 
  array (
    'key' => 'erp.app.assets.index',
    'group' => '扩展与报表',
    'label' => '固定资产',
    'icon' => 'fa-solid fa-building-columns',
    'route' => 'erp.assets.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.asset_manager',
    ),
    'order' => 43,
    'source' => 'app.assets.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 43,
    'children' => 
    array (
    ),
  ),
  43 => 
  array (
    'key' => 'erp.app.ecommerce.index',
    'group' => '扩展与报表',
    'label' => '电商订单',
    'icon' => 'fa-solid fa-shop',
    'route' => 'erp.ecommerce.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.viewer',
      1 => 'erp.ecommerce_operator',
    ),
    'order' => 44,
    'source' => 'app.ecommerce.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 44,
    'children' => 
    array (
    ),
  ),
  44 => 
  array (
    'key' => 'erp.app.reports.index',
    'group' => '扩展与报表',
    'label' => '报表中心',
    'icon' => 'fa-solid fa-chart-simple',
    'route' => 'erp.reports.index',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 45,
    'source' => 'app.reports.index',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 45,
    'children' => 
    array (
    ),
  ),
  45 => 
  array (
    'key' => 'erp.app.approvals.my-tasks',
    'group' => '扩展与报表',
    'label' => '我的审批待办',
    'icon' => 'fa-solid fa-list-check',
    'route' => 'erp.approvals.my-tasks',
    'permission' => NULL,
    'roles' => 
    array (
      0 => 'erp.sales_manager',
      1 => 'erp.purchase_manager',
      2 => 'erp.production_manager',
      3 => 'erp.warehouse_manager',
      4 => 'erp.quality_manager',
      5 => 'erp.accountant',
      6 => 'erp.viewer',
      7 => 'erp.hr_specialist',
      8 => 'erp.project_manager',
      9 => 'erp.asset_manager',
      10 => 'erp.ecommerce_operator',
    ),
    'order' => 46,
    'source' => 'app.approvals.my-tasks',
    'note' => NULL,
    'module' => 'erp',
    'sort' => 46,
    'children' => 
    array (
    ),
  ),
);
