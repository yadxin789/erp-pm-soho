<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 审计追溯中心相关表迁移
 * 包含：商品变更审计、订单状态审计、导出日志审计、对账差异审计、审批流程审计
 */
return new class extends Migration
{
    public function up(): void
    {
        // ==================== 商品变更审计表 ====================
        Schema::create('soho_audit_product_changes', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id');
            // 商品名称
            $table->string('product_name', 255)->comment('商品名称');
            // 变更字段（如 name/price/status 等）
            $table->string('changed_field', 100)->comment('变更字段');
            // 操作类型：create新增/update修改/delete删除
            $table->string('operation_type', 20)->comment('操作类型');
            // 旧值
            $table->text('old_value')->nullable()->comment('旧值');
            // 新值
            $table->text('new_value')->nullable()->comment('新值');
            // 操作人
            $table->string('operator', 100)->comment('操作人');
            // 操作时间
            $table->timestamp('operated_at')->comment('操作时间');

            // 索引
            $table->index(['tenant_id', 'operated_at'], 'ix_107_1');
            $table->index(['tenant_id', 'operation_type'], 'ix_107_2');
            $table->index(['product_name'], 'ix_107_3');
        });

        // ==================== 订单状态审计表 ====================
        Schema::create('soho_audit_order_statuses', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id');
            // 订单号
            $table->string('order_no', 50)->comment('订单号');
            // 旧状态
            $table->string('old_status', 50)->comment('旧状态');
            // 新状态
            $table->string('new_status', 50)->comment('新状态');
            // 操作人
            $table->string('operator', 100)->comment('操作人');
            // 操作时间
            $table->timestamp('operated_at')->comment('操作时间');

            // 索引
            $table->index(['tenant_id', 'operated_at'], 'ix_107_4');
            $table->index(['tenant_id', 'new_status'], 'ix_107_5');
            $table->index(['order_no'], 'ix_107_6');
        });

        // ==================== 导出日志审计表 ====================
        Schema::create('soho_audit_export_logs', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id');
            // 导出单号
            $table->string('export_no', 50)->comment('导出单号');
            // 文件名
            $table->string('file_name', 255)->comment('文件名');
            // 导出模块：order/product/finance/inventory/customer/report
            $table->string('module', 50)->comment('导出模块');
            // 状态：success成功/failed失败
            $table->string('status', 20)->comment('状态');
            // 操作人
            $table->string('operator', 100)->comment('操作人');
            // 导出时间
            $table->timestamp('exported_at')->comment('导出时间');

            // 索引
            $table->index(['tenant_id', 'exported_at'], 'ix_107_7');
            $table->index(['tenant_id', 'status'], 'ix_107_8');
            $table->index(['tenant_id', 'module'], 'ix_107_9');
        });

        // ==================== 对账差异审计表 ====================
        Schema::create('soho_audit_reconciliation_diffs', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id');
            // 对账单号
            $table->string('reconciliation_no', 50)->comment('对账单号');
            // 平台
            $table->string('platform', 50)->comment('平台');
            // 差异类型：amount_mismatch/missing_order/duplicate_order/fee_error/refund_error
            $table->string('diff_type', 50)->comment('差异类型');
            // 处理状态：pending待处理/resolved已处理
            $table->string('status', 20)->default('pending')->comment('处理状态');
            // 差异金额
            $table->decimal('diff_amount', 15, 2)->default(0)->comment('差异金额');
            // 创建时间
            $table->timestamp('created_at')->comment('创建时间');

            // 索引
            $table->index(['tenant_id', 'created_at'], 'ix_107_10');
            $table->index(['tenant_id', 'status'], 'ix_107_11');
            $table->index(['tenant_id', 'platform'], 'ix_107_12');
        });

        // ==================== 审批流程审计表 ====================
        Schema::create('soho_audit_approval_flows', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id');
            // 审批单号
            $table->string('approval_no', 50)->comment('审批单号');
            // 审批类型：product_publish/price_change/order_cancel/inventory_adjust/refund/purchase
            $table->string('type', 50)->comment('审批类型');
            // 审批结果：approved已通过/rejected已拒绝
            $table->string('result', 20)->comment('审批结果');
            // 申请人
            $table->string('applicant', 100)->comment('申请人');
            // 审批人
            $table->string('approver', 100)->comment('审批人');
            // 审批时间
            $table->timestamp('approved_at')->comment('审批时间');

            // 索引
            $table->index(['tenant_id', 'approved_at'], 'ix_107_13');
            $table->index(['tenant_id', 'result'], 'ix_107_14');
            $table->index(['tenant_id', 'type'], 'ix_107_15');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_audit_approval_flows');
        Schema::dropIfExists('soho_audit_reconciliation_diffs');
        Schema::dropIfExists('soho_audit_export_logs');
        Schema::dropIfExists('soho_audit_order_statuses');
        Schema::dropIfExists('soho_audit_product_changes');
    }
};
