<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 补建缺失的业务表
 * 包含：物流账单、物流异常、平台结算单、退款流水、财务差异审批
 * 这些表在控制器中被引用但缺少迁移文件，此处统一补建
 */
return new class extends Migration
{
    public function up(): void
    {
        // 物流账单表
        Schema::create('soho_logistics_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('bill_no')->comment('账单号');
            $table->string('carrier_bill_no')->nullable()->comment('快递公司账单号');
            $table->string('express_company', 20)->comment('快递公司编码');
            $table->string('status', 20)->default('pending')->comment('对账状态: pending/reconciled/disputed');
            $table->date('bill_date')->nullable()->comment('账单日期');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('账单总金额');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_110_1');
            $table->index(['tenant_id', 'bill_date'], 'ix_110_2');
        });

        // 物流异常表
        Schema::create('soho_logistics_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('waybill_no')->comment('运单号');
            $table->string('tracking_no')->nullable()->comment('跟踪号');
            $table->string('order_no')->nullable()->comment('订单号');
            $table->string('exception_type', 30)->comment('异常类型: lost/damaged/wrong_address/rejected/delayed');
            $table->string('severity', 20)->default('low')->comment('异常级别: low/medium/high/urgent');
            $table->string('status', 20)->default('pending')->comment('处理状态: pending/processing/resolved');
            $table->text('description')->nullable()->comment('异常描述');
            $table->timestamp('occurred_at')->nullable()->comment('发生时间');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_110_3');
            $table->index(['tenant_id', 'exception_type'], 'ix_110_4');
            $table->index(['tenant_id', 'occurred_at'], 'ix_110_5');
        });

        // 平台结算单表
        Schema::create('soho_platform_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('settlement_no')->comment('结算单号');
            $table->string('platform', 30)->comment('平台');
            $table->foreignId('shop_id')->nullable()->comment('店铺ID');
            $table->string('status', 20)->default('pending')->comment('状态: pending/settled/disputed/rejected');
            $table->date('period_start')->nullable()->comment('结算周期开始');
            $table->date('period_end')->nullable()->comment('结算周期结束');
            $table->decimal('amount', 12, 2)->default(0)->comment('结算金额');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_110_6');
            $table->index(['tenant_id', 'platform'], 'ix_110_7');
            $table->index(['tenant_id', 'period_start'], 'ix_110_8');
        });

        // 退款流水表
        Schema::create('soho_refund_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('refund_no')->comment('退款单号');
            $table->string('order_no')->nullable()->comment('订单号');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('status', 20)->default('pending')->comment('状态: pending/approved/refunded/rejected');
            $table->decimal('amount', 12, 2)->default(0)->comment('退款金额');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_110_9');
            $table->index(['tenant_id', 'created_at'], 'ix_110_10');
        });

        // 财务差异审批表
        Schema::create('soho_finance_diff_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('doc_no')->comment('单号');
            $table->string('applicant_name')->nullable()->comment('申请人姓名');
            $table->string('type', 30)->comment('类型: order/refund/settlement/payment/other');
            $table->string('status', 20)->default('pending')->comment('状态: pending/approved/rejected');
            $table->decimal('amount', 12, 2)->default(0)->comment('涉及金额');
            $table->decimal('diff_amount', 12, 2)->default(0)->comment('差异金额');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->index(['tenant_id', 'status'], 'ix_110_11');
            $table->index(['tenant_id', 'type'], 'ix_110_12');
            $table->index(['tenant_id', 'created_at'], 'ix_110_13');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_finance_diff_approvals');
        Schema::dropIfExists('soho_refund_flows');
        Schema::dropIfExists('soho_platform_settlements');
        Schema::dropIfExists('soho_logistics_exceptions');
        Schema::dropIfExists('soho_logistics_bills');
    }
};
