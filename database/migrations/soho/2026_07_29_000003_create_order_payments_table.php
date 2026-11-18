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
        Schema::create('soho_order_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->string('payment_method', 50)->comment('支付方式：alipay/wechat/credit_card/bank_transfer等');
            $table->string('payment_status', 30)->default('pending')->comment('支付状态：pending(待支付), paid(已支付), failed(失败), refunded(已退款)');
            $table->decimal('amount', 18, 4)->default(0)->comment('支付金额');
            $table->string('transaction_no', 100)->nullable()->comment('交易流水号');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->json('raw_data')->nullable()->comment('支付原始数据');
            $table->timestamps();

            $table->index(['order_id'], 'ix_43_1');
            $table->index(['transaction_no'], 'ix_43_2');
            $table->index(['tenant_id', 'order_id'], 'ix_43_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_order_payments');
    }
};