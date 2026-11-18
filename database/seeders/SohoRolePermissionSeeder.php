<?php

namespace Database\Seeders;

use App\Soho\Models\Department;
use App\Soho\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * 角色和权限初始化器
 * 对应审计报告 V2.1 §6 用户、角色、权限和审批
 * 包含 10 个主角色 + 19 个岗位子角色，共 29 个角色
 */
class SohoRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== 创建权限（审计报告 §6.2 权限分层） ==========
        $permissions = [
            // 仪表盘
            'dashboard.view',

            // 部门管理
            'department.view', 'department.create', 'department.edit', 'department.delete',

            // 用户管理
            'user.view', 'user.create', 'user.edit', 'user.delete',

            // 角色管理
            'role.view', 'role.create', 'role.edit', 'role.delete',

            // 系统管理
            'system.view', 'system.config',

            // 安全管理（新增 - 审计报告 §6.1 安全管理员）
            'security.view', 'security.config', 'security.ip_whitelist', 'security.key_management',

            // 店铺管理
            'shop.view', 'shop.create', 'shop.edit', 'shop.delete',
            'shop.authorize', 'shop.sync',

            // 平台账号管理（新增 - 审计报告 §7）
            'platform_account.view', 'platform_account.create', 'platform_account.edit',
            'platform_account.authorize', 'platform_account.sync',

            // 商品管理
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'product.publish', 'product.publish.request', 'product.publish.approve',
            'product.import',

            // 商品审核（新增 - 审计报告 §6.1 商品审核员）
            'product.review', 'product.review.approve', 'product.review.reject',

            // SKU 管理
            'sku.view', 'sku.create', 'sku.edit', 'sku.delete',

            // 品类/品牌
            'category.view', 'category.create', 'category.edit', 'category.delete',
            'brand.view', 'brand.create', 'brand.edit', 'brand.delete',

            // 商品属性
            'product.attribute.view', 'product.attribute.create', 'product.attribute.edit', 'product.attribute.delete',

            // 媒体库
            'media.view', 'media.upload', 'media.delete',

            // 仓库管理
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'warehouse.location.manage',

            // 库存管理（增强 - 审计报告 §6.2 库存权限分层）
            'inventory.view',
            'inventory.adjust', 'inventory.adjust.request', 'inventory.adjust.approve',
            'inventory.transfer',
            'inventory.stocktake',
            'inventory.batch.view',
            'inventory.document.view', 'inventory.document.create',

            // 订单管理（增强 - 审计报告 §6.2 订单权限分层）
            'order.view', 'order.create', 'order.edit', 'order.delete',
            'order.ship', 'order.ship.create', 'order.ship.approve',
            'order.export', 'order.sensitive_view',
            'order.cancel', 'order.cancel.request', 'order.cancel.approve',
            'order.split', 'order.merge',

            // 包裹管理
            'package.view', 'package.create', 'package.edit',

            // 物流管理
            'express.view', 'express.create', 'express.edit', 'express.delete',
            'logistics.view', 'logistics.channel.manage',

            // 售后管理（增强 - 审计报告 §6.2 退款权限分层）
            'after_sale.view', 'after_sale.approve', 'after_sale.reject',
            'after_sale.refund',
            'refund.request', 'refund.approve', 'refund.execute', 'refund.export',

            // 财务管理（增强 - 审计报告 §6.2 财务权限分层）
            'finance.view', 'finance.sensitive_view',
            'finance.reconcile', 'finance.reconcile.approve',
            'finance.settle', 'finance.export',

            // 价格管理（V3.1 增强 - 定价人员完整权限）
            'price.view', 'price.rule.view', 'price.rule.create',
            'price.change.request', 'price.change.approve', 'price.change.execute', 'price.change.rollback',
            'price.update', 'price.approve',

            // 采购管理（V3.1 新增）
            'procurement.view', 'procurement.plan.view', 'procurement.plan.create',
            'procurement.request.view', 'procurement.request.create',
            'procurement.order.view', 'procurement.order.create', 'procurement.order.approve',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'procurement.arrival', 'procurement.inbound',
            'report.procurement',

            // 报表
            'report.sales', 'report.product', 'report.stock', 'report.profit',
            'report.finance', 'report.aftersale',

            // 同步任务
            'sync_task.view', 'sync_task.execute',
            'sync_cursor.view', 'sync_cursor.reset',

            // 日志
            'log.view', 'log.api', 'log.audit',

            // 客户管理
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'customer.sensitive_view', 'customer.export',

            // 促销管理
            'promotion.view', 'promotion.create', 'promotion.edit', 'promotion.delete',
            'coupon.view', 'coupon.create', 'coupon.edit', 'coupon.delete',

            // 税务与货币
            'tax_rate.view', 'tax_rate.create', 'tax_rate.edit', 'tax_rate.delete',
            'currency.view', 'currency.create', 'currency.edit', 'currency.delete', 'currency.manage',
            'tax_entity.view', 'tax_entity.manage',
            'exchange_rate.view', 'exchange_rate.manage',

            // 物流配送
            'shipping.view', 'shipping.create', 'shipping.edit', 'shipping.delete',

            // 事件系统
            'event.view', 'event.process', 'event.replay',

            // 审批流（新增 - 审计报告 §6.3 审批规则）
            'approval_flow.view', 'approval_flow.manage',
            'approval.instance.view', 'approval.instance.approve', 'approval.instance.reject',

            // 导出任务（新增）
            'export_task.view', 'export_task.create',

            // 异常管理（新增 - 审计报告 §18 异常中心）
            'exception.view', 'exception.handle', 'exception.assign',

            // 数据权限（新增 - 审计报告 §3.9 数据权限）
            'data_scope.manage',

            // V3.1 仓库岗位权限
            'picking.execute', 'picking.view',
            'checking.execute', 'checking.view',
            'packing.execute', 'packing.view',
            'fulfillment.view',

            // V3.1 物流扩展权限
            'logistics.bill.view',
            'logistics.callback',

            // V3.1 售后扩展权限
            'after_sale.handle',

            // V3.1 采购扩展权限
            'purchase.view', 'purchase.approve',

            // V3.1 商品扩展权限
            'sku.view', 'product.attribute.view', 'product.approve', 'product.review',
            'category.view',

            // V3.1 运营审批权限
            'price.change.approve', 'order.approve', 'inventory.approve', 'refund.approve',

            // V3.1 退货权限
            'return.receive', 'return.inspect',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ========== 创建角色（审计报告 §6.1 标准角色） ==========

        // 1. 超级管理员 - 全部权限
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. 租户管理员 - 租户配置、组织、用户和权限
        $tenantAdmin = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $tenantAdmin->syncPermissions([
            'dashboard.view',
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'system.view', 'system.config',
            'log.view', 'log.audit',
            'data_scope.manage',
            'approval_flow.view', 'approval_flow.manage',
        ]);

        // 3. 安全管理员 - 登录安全、密钥、IP白名单和安全策略
        $securityAdmin = Role::firstOrCreate(['name' => 'security_admin', 'guard_name' => 'web']);
        $securityAdmin->syncPermissions([
            'dashboard.view',
            'security.view', 'security.config', 'security.ip_whitelist', 'security.key_management',
            'log.view', 'log.audit', 'log.api',
            'system.view',
        ]);

        // 4. 店铺授权管理员 - 平台授权、Token、店铺配置
        $shopAuthAdmin = Role::firstOrCreate(['name' => 'shop_auth_admin', 'guard_name' => 'web']);
        $shopAuthAdmin->syncPermissions([
            'dashboard.view',
            'shop.view', 'shop.create', 'shop.edit', 'shop.authorize', 'shop.sync',
            'platform_account.view', 'platform_account.create', 'platform_account.edit',
            'platform_account.authorize', 'platform_account.sync',
            'log.api',
            'sync_task.view',
        ]);

        // 5. 运营主管 - 店铺、商品、订单、报表审批
        $operationManager = Role::firstOrCreate(['name' => 'operation_manager', 'guard_name' => 'web']);
        $operationManager->syncPermissions([
            'dashboard.view',
            'shop.view', 'shop.edit', 'shop.authorize',
            'product.view', 'product.edit', 'product.publish.approve',
            'sku.view',
            'order.view', 'order.export', 'order.cancel.approve',
            'inventory.view',
            'warehouse.view',
            'report.sales', 'report.product', 'report.stock', 'report.profit',
            'report.aftersale',
            'customer.view',
            'promotion.view', 'promotion.edit',
            'approval.instance.view', 'approval.instance.approve',
            'price.approve',
            // 价格查看权限（运营主管需监督价格策略，查看价格查询/计算/历史）
            'price.view',
            // V3.1 新增审批权限
            'product.approve', 'price.change.approve', 'order.approve',
            'inventory.approve', 'refund.approve',
            'after_sale.view',
            'order.edit',
            'finance.reconcile',
        ]);

        // 6. 平台运营人员 - 店铺运营、商品上架、价格和活动
        $platformOperator = Role::firstOrCreate(['name' => 'platform_operator', 'guard_name' => 'web']);
        $platformOperator->syncPermissions([
            'dashboard.view',
            'shop.view', 'shop.edit',
            'product.view', 'product.edit', 'product.publish.request',
            'sku.view', 'sku.edit',
            'category.view', 'product.attribute.view',
            'media.view', 'media.upload',
            'report.sales', 'report.product',
            'promotion.view', 'promotion.create', 'promotion.edit',
            'coupon.view', 'coupon.create',
            'price.update',
            // 调价申请权限（平台运营人员可发起调价申请）
            'price.change.request',
        ]);

        // 7. 商品管理员 - SPU、SKU、类目、属性、图片
        $productAdmin = Role::firstOrCreate(['name' => 'product_admin', 'guard_name' => 'web']);
        $productAdmin->syncPermissions([
            'dashboard.view',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'product.publish', 'product.import',
            'sku.view', 'sku.create', 'sku.edit', 'sku.delete',
            'category.view', 'category.create', 'category.edit', 'category.delete',
            'brand.view', 'brand.create', 'brand.edit', 'brand.delete',
            'report.product',
            'product.attribute.view', 'product.attribute.create', 'product.attribute.edit', 'product.attribute.delete',
            'media.view', 'media.upload', 'media.delete',
        ]);

        // 8. 商品审核员 - 商品资料审核和发布审批
        $productReviewer = Role::firstOrCreate(['name' => 'product_reviewer', 'guard_name' => 'web']);
        $productReviewer->syncPermissions([
            'dashboard.view',
            'product.view', 'product.review', 'product.review.approve', 'product.review.reject',
            'sku.view',
            'category.view',
            'brand.view',
            'report.product',
            'media.view',
        ]);

        // 9. 定价人员 - 价格规则、调价任务（V3.1 增强）
        $pricingStaff = Role::firstOrCreate(['name' => 'pricing_staff', 'guard_name' => 'web']);
        $pricingStaff->syncPermissions([
            'dashboard.view',
            'product.view',
            'sku.view', 'sku.edit',
            'price.view', 'price.rule.view', 'price.rule.create',
            'price.change.request', 'price.change.execute', 'price.change.rollback',
            'price.update',
            'report.sales', 'report.product', 'report.profit',
        ]);

        // 10. 订单主管 - 订单异常、取消、发货审核
        $orderManager = Role::firstOrCreate(['name' => 'order_manager', 'guard_name' => 'web']);
        $orderManager->syncPermissions([
            'dashboard.view',
            'order.view', 'order.create', 'order.edit',
            'order.ship.create', 'order.ship.approve',
            'order.export', 'order.sensitive_view',
            'order.cancel.request', 'order.cancel.approve',
            'order.split', 'order.merge',
            'package.view', 'package.create',
            'express.view',
            'report.sales',
            'approval.instance.view', 'approval.instance.approve',
            // V3.1 订单扩展权限
            'fulfillment.view',
        ]);

        // 11. 客服 - 订单备注、售后、退款申请
        $customerService = Role::firstOrCreate(['name' => 'customer_service', 'guard_name' => 'web']);
        $customerService->syncPermissions([
            'dashboard.view',
            'order.view',
            'after_sale.view', 'after_sale.approve', 'after_sale.reject', 'after_sale.refund',
            'refund.request',
            'customer.view', 'customer.edit',
            'express.view',
        ]);

        // 12. 仓库主管 - 仓库、出入库、盘点、调拨
        $warehouseManager = Role::firstOrCreate(['name' => 'warehouse_manager', 'guard_name' => 'web']);
        $warehouseManager->syncPermissions([
            'dashboard.view',
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'warehouse.location.manage',
            'inventory.view',
            'inventory.adjust', 'inventory.adjust.request', 'inventory.adjust.approve',
            'inventory.transfer', 'inventory.stocktake',
            'inventory.batch.view',
            'inventory.document.view', 'inventory.document.create',
            'sku.view',
            'package.view', 'package.create', 'package.edit',
            'report.stock',
            'shipping.view',
            'approval.instance.view', 'approval.instance.approve',
            // V3.1 仓库扩展权限
            'return.receive', 'return.inspect',
            'fulfillment.view',
        ]);

        // 13. 拣货员 - 拣货任务
        $picker = Role::firstOrCreate(['name' => 'picker', 'guard_name' => 'web']);
        $picker->syncPermissions([
            'dashboard.view',
            'warehouse.view',
            'inventory.view',
            'inventory.document.view',
            'sku.view',
            'warehouse.location.manage',
            'picking.execute', 'picking.view',
        ]);

        // 14. 复核员 - 商品复核
        $checker = Role::firstOrCreate(['name' => 'checker', 'guard_name' => 'web']);
        $checker->syncPermissions([
            'dashboard.view',
            'warehouse.view',
            'inventory.view',
            'package.view',
            'sku.view',
            'checking.execute', 'checking.view',
        ]);

        // 15. 打包员 - 打包和面单
        $packer = Role::firstOrCreate(['name' => 'packer', 'guard_name' => 'web']);
        $packer->syncPermissions([
            'dashboard.view',
            'warehouse.view',
            'package.view', 'package.create', 'package.edit',
            'shipping.view',
            'express.view',
            'packing.execute', 'packing.view',
        ]);

        // 16. 物流专员 - 物流渠道、包裹和轨迹
        $logisticsSpecialist = Role::firstOrCreate(['name' => 'logistics_specialist', 'guard_name' => 'web']);
        $logisticsSpecialist->syncPermissions([
            'dashboard.view',
            'express.view', 'express.create', 'express.edit', 'express.delete',
            'logistics.view', 'logistics.channel.manage',
            'package.view', 'package.edit',
            'shipping.view', 'shipping.create', 'shipping.edit',
            'logistics.bill.view', 'logistics.callback',
        ]);

        // 17. 售后主管 - 售后审批、退款审核、争议处理
        $aftersaleManager = Role::firstOrCreate(['name' => 'aftersale_manager', 'guard_name' => 'web']);
        $aftersaleManager->syncPermissions([
            'dashboard.view',
            'order.view',
            'after_sale.view', 'after_sale.approve', 'after_sale.reject',
            'refund.request', 'refund.approve', 'refund.execute', 'refund.export',
            'express.view',
            'approval.instance.view', 'approval.instance.approve',
            'report.aftersale',
            'after_sale.handle',
        ]);

        // 18. 财务人员 - 收款、退款、账单和对账
        $financeStaff = Role::firstOrCreate(['name' => 'finance_staff', 'guard_name' => 'web']);
        $financeStaff->syncPermissions([
            'dashboard.view',
            'order.view',
            'finance.view', 'finance.reconcile', 'finance.settle',
            'finance.export',
            'report.sales', 'report.profit', 'report.finance',
            'log.view',
            'currency.view', 'exchange_rate.view',
            'finance.sensitive_view',
        ]);

        // 19. 财务主管 - 对账审批、利润口径和结算确认
        $financeManager = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'web']);
        $financeManager->syncPermissions([
            'dashboard.view',
            'order.view',
            'finance.view', 'finance.sensitive_view',
            'finance.reconcile', 'finance.reconcile.approve',
            'finance.settle',
            'finance.export',
            'report.sales', 'report.profit', 'report.finance',
            'log.view', 'log.audit',
            'currency.view', 'currency.manage',
            'tax_entity.view', 'tax_entity.manage',
            'exchange_rate.view', 'exchange_rate.manage',
            'approval.instance.view', 'approval.instance.approve',
            'finance.settle',
        ]);

        // 20. 采购员 - 采购单、供应商和采购入库（V3.1 增强）
        $procurementStaff = Role::firstOrCreate(['name' => 'procurement_staff', 'guard_name' => 'web']);
        $procurementStaff->syncPermissions([
            'dashboard.view',
            'product.view', 'sku.view',
            'inventory.view', 'inventory.batch.view',
            'warehouse.view',
            'procurement.view', 'procurement.plan.view', 'procurement.plan.create',
            'procurement.request.view', 'procurement.request.create',
            'procurement.order.view', 'procurement.order.create',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'procurement.arrival', 'procurement.inbound',
            'report.stock', 'report.procurement',
            'purchase.view',
        ]);

        // 21. 数据分析员 - 报表和数据导出
        $dataAnalyst = Role::firstOrCreate(['name' => 'data_analyst', 'guard_name' => 'web']);
        $dataAnalyst->syncPermissions([
            'dashboard.view',
            'product.view', 'sku.view',
            'warehouse.view', 'inventory.view',
            'order.view',
            'after_sale.view',
            'express.view',
            'report.sales', 'report.product', 'report.stock', 'report.profit', 'report.finance', 'report.aftersale',
            'finance.view',
            'customer.view',
            'export_task.view', 'export_task.create',
        ]);

        // 22. 集成运维 - 同步任务、API错误和Webhook
        $integrationOps = Role::firstOrCreate(['name' => 'integration_ops', 'guard_name' => 'web']);
        $integrationOps->syncPermissions([
            'dashboard.view',
            'shop.view',
            'sync_task.view', 'sync_task.execute',
            'sync_cursor.view', 'sync_cursor.reset',
            'log.view', 'log.api',
            'exception.view', 'exception.handle',
            'event.view', 'event.process', 'event.replay',
            'system.view',  // V3.1：允许查看系统健康、队列监控
        ]);

        // 23. 审计员 - 只读审计和合规检查
        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'dashboard.view',
            'product.view', 'sku.view',
            'warehouse.view', 'inventory.view',
            'order.view', 'order.sensitive_view',
            'after_sale.view',
            'express.view',
            'finance.view', 'finance.sensitive_view',
            'report.sales', 'report.product', 'report.stock', 'report.profit', 'report.finance',
            'log.view', 'log.audit', 'log.api',
            'event.view',
            'exception.view',
            'approval_flow.view', 'approval.instance.view',
            'data_scope.manage',
        ]);

        // ========== V3.1 新增角色 ==========

        // 25. 营销专员 - 平台促销、内部促销、优惠券管理（V3.1 新增）
        $marketingStaff = Role::firstOrCreate(['name' => 'marketing_staff', 'guard_name' => 'web']);
        $marketingStaff->syncPermissions([
            'dashboard.view',
            'promotion.view', 'promotion.create', 'promotion.edit',
            'coupon.view', 'coupon.create', 'coupon.edit',
            'report.sales', 'report.product',
            'shop.view',
        ]);

        // 26. 供应链主管 - 采购审批、供应商准入、采购价格审批（V3.1 新增）
        $procurementManager = Role::firstOrCreate(['name' => 'procurement_manager', 'guard_name' => 'web']);
        $procurementManager->syncPermissions([
            'dashboard.view',
            'procurement.view', 'procurement.order.view', 'procurement.order.approve',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'price.view', 'price.change.approve',
            'inventory.view', 'inventory.batch.view',
            'report.stock', 'report.procurement',
            'approval.instance.view',
        ]);

        // 24. 只读用户 - 只读，不得导出敏感数据
        $readOnly = Role::firstOrCreate(['name' => 'read_only', 'guard_name' => 'web']);
        $readOnly->syncPermissions([
            'dashboard.view',
            'product.view', 'sku.view',
            'warehouse.view', 'inventory.view',
            'order.view',
            'after_sale.view',
            'express.view',
            'report.sales', 'report.product', 'report.stock', 'report.profit',
            'log.view',
        ]);

        // ========== 创建默认部门 ==========
        $depts = [
            ['name' => '总经办', 'sort_order' => 1],
            ['name' => '运营部', 'sort_order' => 2],
            ['name' => '订单部', 'sort_order' => 3],
            ['name' => '仓储部', 'sort_order' => 4],
            ['name' => '客服部', 'sort_order' => 5],
            ['name' => '财务部', 'sort_order' => 6],
            ['name' => '安全部', 'sort_order' => 7],
            ['name' => '技术部', 'sort_order' => 8],
            ['name' => '审计部', 'sort_order' => 9],
        ];

        foreach ($depts as $i => $dept) {
            Department::firstOrCreate(['name' => $dept['name']], array_merge($dept, ['code' => 'soho_dept_' . ($i + 1), 'module' => 'soho']));
        }

        // ========== 创建默认账号 ==========
        $this->createDefaultUsers();

        $this->command->info('角色和权限初始化完成！');
        $this->command->info('账号列表（共26个角色 - V3.1）：');
        $this->command->info('  admin@ddwl-soho.com          - 超级管理员');
        $this->command->info('  tenant@ddwl-soho.com         - 租户管理员');
        $this->command->info('  security@ddwl-soho.com       - 安全管理员');
        $this->command->info('  shop_auth@ddwl-soho.com      - 店铺授权管理员');
        $this->command->info('  operation@ddwl-soho.com      - 运营主管');
        $this->command->info('  platform_op@ddwl-soho.com    - 平台运营人员');
        $this->command->info('  product@ddwl-soho.com        - 商品管理员');
        $this->command->info('  reviewer@ddwl-soho.com      - 商品审核员');
        $this->command->info('  pricing@ddwl-soho.com        - 定价人员');
        $this->command->info('  order_mgr@ddwl-soho.com      - 订单主管');
        $this->command->info('  cs@ddwl-soho.com             - 客服');
        $this->command->info('  warehouse_mgr@ddwl-soho.com - 仓库主管');
        $this->command->info('  picker@ddwl-soho.com         - 拣货员');
        $this->command->info('  checker@ddwl-soho.com        - 复核员');
        $this->command->info('  packer@ddwl-soho.com         - 打包员');
        $this->command->info('  logistics@ddwl-soho.com      - 物流专员');
        $this->command->info('  aftersale_mgr@ddwl-soho.com - 售后主管');
        $this->command->info('  finance@ddwl-soho.com        - 财务人员');
        $this->command->info('  finance_mgr@ddwl-soho.com   - 财务主管');
        $this->command->info('  procurement@ddwl-soho.com   - 采购员');
        $this->command->info('  analyst@ddwl-soho.com        - 数据分析员');
        $this->command->info('  integrations@ddwl-soho.com  - 集成运维');
        $this->command->info('  auditor@ddwl-soho.com        - 审计员');
        $this->command->info('  viewer@ddwl-soho.com         - 只读用户');
        $this->command->info('  marketing@ddwl-soho.com      - 营销专员(V3.1新增)');
        $this->command->info('  procurement_mgr@ddwl-soho.com - 供应链主管(V3.1新增)');
        $this->command->info('  密码均为: admin123456');
    }

    /**
     * 创建默认用户账号
     */
    private function createDefaultUsers(): void
    {
        // 查找各部门
        $adminDept = Department::where('name', '总经办')->first();
        $operationDept = Department::where('name', '运营部')->first();
        $orderDept = Department::where('name', '订单部')->first();
        $warehouseDept = Department::where('name', '仓储部')->first();
        $csDept = Department::where('name', '客服部')->first();
        $financeDept = Department::where('name', '财务部')->first();
        $securityDept = Department::where('name', '安全部')->first();
        $techDept = Department::where('name', '技术部')->first();
        $auditDept = Department::where('name', '审计部')->first();

        $users = [
            ['email' => 'admin@ddwl-soho.com', 'name' => 'admin', 'real_name' => '系统超级管理员', 'role' => 'super_admin', 'dept' => $adminDept],
            ['email' => 'tenant@ddwl-soho.com', 'name' => 'tenant', 'real_name' => '租户管理员', 'role' => 'tenant_admin', 'dept' => $adminDept],
            ['email' => 'security@ddwl-soho.com', 'name' => 'security', 'real_name' => '安全管理员', 'role' => 'security_admin', 'dept' => $securityDept],
            ['email' => 'shop_auth@ddwl-soho.com', 'name' => 'shop_auth', 'real_name' => '店铺授权管理员', 'role' => 'shop_auth_admin', 'dept' => $adminDept],
            ['email' => 'operation@ddwl-soho.com', 'name' => 'operation', 'real_name' => '运营主管', 'role' => 'operation_manager', 'dept' => $operationDept],
            ['email' => 'platform_op@ddwl-soho.com', 'name' => 'platform_op', 'real_name' => '平台运营人员', 'role' => 'platform_operator', 'dept' => $operationDept],
            ['email' => 'product@ddwl-soho.com', 'name' => 'product', 'real_name' => '商品管理员', 'role' => 'product_admin', 'dept' => $operationDept],
            ['email' => 'reviewer@ddwl-soho.com', 'name' => 'reviewer', 'real_name' => '商品审核员', 'role' => 'product_reviewer', 'dept' => $operationDept],
            ['email' => 'pricing@ddwl-soho.com', 'name' => 'pricing', 'real_name' => '定价人员', 'role' => 'pricing_staff', 'dept' => $operationDept],
            ['email' => 'order_mgr@ddwl-soho.com', 'name' => 'order_mgr', 'real_name' => '订单主管', 'role' => 'order_manager', 'dept' => $orderDept],
            ['email' => 'cs@ddwl-soho.com', 'name' => 'cs', 'real_name' => '客服', 'role' => 'customer_service', 'dept' => $csDept],
            ['email' => 'warehouse_mgr@ddwl-soho.com', 'name' => 'warehouse_mgr', 'real_name' => '仓库主管', 'role' => 'warehouse_manager', 'dept' => $warehouseDept],
            ['email' => 'picker@ddwl-soho.com', 'name' => 'picker', 'real_name' => '拣货员', 'role' => 'picker', 'dept' => $warehouseDept],
            ['email' => 'checker@ddwl-soho.com', 'name' => 'checker', 'real_name' => '复核员', 'role' => 'checker', 'dept' => $warehouseDept],
            ['email' => 'packer@ddwl-soho.com', 'name' => 'packer', 'real_name' => '打包员', 'role' => 'packer', 'dept' => $warehouseDept],
            ['email' => 'logistics@ddwl-soho.com', 'name' => 'logistics', 'real_name' => '物流专员', 'role' => 'logistics_specialist', 'dept' => $warehouseDept],
            ['email' => 'aftersale_mgr@ddwl-soho.com', 'name' => 'aftersale_mgr', 'real_name' => '售后主管', 'role' => 'aftersale_manager', 'dept' => $csDept],
            ['email' => 'finance@ddwl-soho.com', 'name' => 'finance', 'real_name' => '财务人员', 'role' => 'finance_staff', 'dept' => $financeDept],
            ['email' => 'finance_mgr@ddwl-soho.com', 'name' => 'finance_mgr', 'real_name' => '财务主管', 'role' => 'finance_manager', 'dept' => $financeDept],
            ['email' => 'procurement@ddwl-soho.com', 'name' => 'procurement', 'real_name' => '采购员', 'role' => 'procurement_staff', 'dept' => $operationDept],
            ['email' => 'analyst@ddwl-soho.com', 'name' => 'analyst', 'real_name' => '数据分析员', 'role' => 'data_analyst', 'dept' => $financeDept],
            ['email' => 'integrations@ddwl-soho.com', 'name' => 'integrations', 'real_name' => '集成运维', 'role' => 'integration_ops', 'dept' => $techDept],
            ['email' => 'auditor@ddwl-soho.com', 'name' => 'auditor', 'real_name' => '审计员', 'role' => 'auditor', 'dept' => $auditDept],
            ['email' => 'viewer@ddwl-soho.com', 'name' => 'viewer', 'real_name' => '只读用户', 'role' => 'read_only', 'dept' => $adminDept],
            // V3.1 新增角色
            ['email' => 'marketing@ddwl-soho.com', 'name' => 'marketing', 'real_name' => '营销专员', 'role' => 'marketing_staff', 'dept' => $operationDept],
            ['email' => 'procurement_mgr@ddwl-soho.com', 'name' => 'procurement_mgr', 'real_name' => '供应链主管', 'role' => 'procurement_manager', 'dept' => $operationDept],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                      'username' => $userData['name'],
                      'module' => 'soho',
                    'real_name' => $userData['real_name'],
                    'password' => bcrypt('admin123456'),
                    'department_id' => $userData['dept']?->id,
                    'status' => 1,
                ]
            );
            $user->assignRole($userData['role']);
        }
    }
}