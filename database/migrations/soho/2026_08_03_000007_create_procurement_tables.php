<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建采购相关表
 * 包含：采购计划、采购申请、采购订单、采购审批、采购入库、采购到货、供应商、安全库存
 */
return new class extends Migration
{
    public function up(): void
    {
        // 供应商表
        Schema::create('soho_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('supplier_no')->nullable()->comment('供应商编号');
            $table->string('name')->comment('供应商名称');
            $table->string('level', 10)->default('B')->comment('等级: A/B/C/D');
            $table->string('contact_person')->nullable()->comment('联系人');
            $table->string('phone')->nullable()->comment('电话');
            $table->string('email')->nullable()->comment('邮箱');
            $table->string('address')->nullable()->comment('地址');
            $table->boolean('status')->default(true)->comment('状态: 1合作中, 0已暂停');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'level'], 'ix_109_1');
            $table->index(['tenant_id', 'status'], 'ix_109_2');
        });

        // 采购计划表
        Schema::create('soho_procurement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('plan_no')->nullable()->comment('计划编号');
            $table->string('name')->comment('计划名称');
            $table->string('type', 50)->nullable()->comment('类型');
            $table->string('owner')->nullable()->comment('负责人');
            $table->decimal('budget', 12, 2)->default(0)->comment('预算金额');
            $table->string('status', 30)->default('draft')->comment('状态: draft/pending/approved/executing/completed/cancelled');
            $table->timestamp('planned_at')->nullable()->comment('计划日期');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_109_3');
            $table->index(['tenant_id', 'planned_at'], 'ix_109_4');
        });

        // 采购申请表
        Schema::create('soho_procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('request_no')->nullable()->comment('申请编号');
            $table->string('title')->comment('申请标题');
            $table->unsignedBigInteger('applicant_id')->nullable()->comment('申请人ID');
            $table->string('status', 30)->default('draft')->comment('状态: draft/pending/approved/rejected/ordered/closed');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status'], 'ix_109_5');
            $table->index(['tenant_id', 'applicant_id'], 'ix_109_6');
        });

        // 采购订单表
        Schema::create('soho_procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('order_no')->nullable()->comment('订单编号');
            $table->foreignId('supplier_id')->nullable()->comment('供应商ID');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('订单金额');
            $table->string('status', 30)->default('pending')->comment('状态: pending/confirmed/shipped/arrived/completed/cancelled');
            $table->timestamp('ordered_at')->nullable()->comment('下单时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_109_7');
            $table->index(['tenant_id', 'supplier_id'], 'ix_109_8');
            $table->index(['tenant_id', 'ordered_at'], 'ix_109_9');
        });

        // 采购审批表
        Schema::create('soho_procurement_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('approval_no')->nullable()->comment('审批编号');
            $table->string('type', 30)->comment('类型: plan/request/order/budget');
            $table->string('title')->nullable()->comment('审批标题');
            $table->string('status', 30)->default('pending')->comment('状态: pending/approved/rejected/returned');
            $table->timestamp('submitted_at')->nullable()->comment('提交时间');
            $table->timestamp('approved_at')->nullable()->comment('审批时间');
            $table->unsignedBigInteger('approver_id')->nullable()->comment('审批人ID');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'type'], 'ix_109_10');
            $table->index(['tenant_id', 'status'], 'ix_109_11');
            $table->index(['tenant_id', 'submitted_at'], 'ix_109_12');
        });

        // 采购入库表
        Schema::create('soho_procurement_inbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('inbound_no')->nullable()->comment('入库编号');
            $table->string('arrival_no')->nullable()->comment('到货编号');
            $table->foreignId('warehouse_id')->nullable()->comment('仓库ID');
            $table->string('status', 30)->default('pending')->comment('状态: pending/processing/completed/cancelled');
            $table->timestamp('inbound_at')->nullable()->comment('入库时间');
            $table->decimal('quantity', 10, 2)->default(0)->comment('入库数量');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_109_13');
            $table->index(['tenant_id', 'warehouse_id'], 'ix_109_14');
            $table->index(['tenant_id', 'inbound_at'], 'ix_109_15');
        });

        // 采购到货表
        Schema::create('soho_procurement_arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('arrival_no')->nullable()->comment('到货编号');
            $table->string('order_no')->nullable()->comment('订单编号');
            $table->string('status', 30)->default('pending')->comment('状态: pending/accepted/rejected/partial');
            $table->timestamp('arrived_at')->nullable()->comment('到货时间');
            $table->decimal('quantity', 10, 2)->default(0)->comment('到货数量');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_109_16');
            $table->index(['tenant_id', 'arrived_at'], 'ix_109_17');
        });

        // 安全库存表
        Schema::create('soho_safe_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('sku_code')->nullable()->comment('SKU编码');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->foreignId('warehouse_id')->nullable()->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->decimal('current_stock', 10, 2)->default(0)->comment('当前库存');
            $table->decimal('safe_stock', 10, 2)->default(0)->comment('安全库存');
            $table->string('alert_level', 20)->default('normal')->comment('预警级别: normal/warning/danger/out');
            $table->timestamps();
            $table->index(['tenant_id', 'warehouse_id'], 'ix_109_18');
            $table->index(['tenant_id', 'alert_level'], 'ix_109_19');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_safe_stocks');
        Schema::dropIfExists('soho_procurement_arrivals');
        Schema::dropIfExists('soho_procurement_inbounds');
        Schema::dropIfExists('soho_procurement_approvals');
        Schema::dropIfExists('soho_procurement_orders');
        Schema::dropIfExists('soho_procurement_requests');
        Schema::dropIfExists('soho_procurement_plans');
        Schema::dropIfExists('soho_suppliers');
    }
};
