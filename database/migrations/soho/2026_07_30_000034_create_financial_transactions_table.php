<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_financial_transactions', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 法人实体ID（税务主体关联）
            $table->unsignedBigInteger('legal_entity_id')->nullable()->comment('法人实体ID');
            // 店铺ID
            $table->foreignId('shop_id')
                ->nullable()
                
                
                ->comment('店铺ID');
            // 平台：taobao/jd/pinduoduo/amazon
            $table->string('platform', 30)->comment('平台');
            // 订单ID
            $table->unsignedBigInteger('order_id')->nullable()->comment('订单ID');
            // 结算ID，关联结算单
            $table->foreignId('settlement_id')
                ->nullable()
                
                
                ->comment('结算ID');
            // 交易类型：income收入/refund退款/commission佣金/reward奖励/payment付款/logistics运费/advertising广告费/warehouse仓租/other其他
            $table->string('transaction_type', 30)->comment('交易类型');
            // 借贷方向：debit借/credit贷
            $table->string('direction', 20)->comment('借贷方向');
            // 交易金额
            $table->decimal('amount', 12, 2)->default(0)->comment('交易金额');
            // 原始金额（外币金额）
            $table->decimal('original_amount', 12, 2)->nullable()->comment('原始金额');
            // 币种
            $table->string('currency', 10)->default('CNY')->comment('币种');
            // 汇率
            $table->decimal('exchange_rate', 12, 6)->nullable()->comment('汇率');
            // 交易流水号（系统唯一）
            $table->string('transaction_no', 100)->unique('ux_74_1')->comment('交易流水号');
            // 平台交易号
            $table->string('platform_trade_no', 100)->nullable()->comment('平台交易号');
            // 交易发生时间
            $table->timestamp('transaction_time')->nullable()->comment('交易发生时间');
            // 记账时间
            $table->timestamp('booking_time')->nullable()->comment('记账时间');
            // 原始数据
            $table->json('raw_data')->nullable()->comment('原始数据(JSON)');
            // 对账状态：unreconciled未对账/reconciled已对账/difference有差异
            $table->string('reconcile_status', 20)->default('unreconciled')->comment('对账状态');
            $table->timestamps();

            // 租户+店铺索引
            $table->index(['tenant_id', 'shop_id'], 'ix_74_2');
            // 租户+交易类型索引
            $table->index(['tenant_id', 'transaction_type'], 'ix_74_3');
            // 租户+对账状态索引
            $table->index(['tenant_id', 'reconcile_status'], 'ix_74_4');
            // 租户+交易时间索引
            $table->index(['tenant_id', 'transaction_time'], 'ix_74_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_financial_transactions');
    }
};