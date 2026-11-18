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
        Schema::create('soho_refund_records', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 售后ID，关联售后主表
            $table->foreignId('after_sale_id')
                
                
                ->comment('售后ID');
            // 订单ID
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            // 退款单号
            $table->string('refund_no', 50)->unique('ux_71_1')->comment('退款单号');
            // 退款类型：full全额/partial部分退款
            $table->string('refund_type', 30)->comment('退款类型');
            // 退款金额
            $table->decimal('refund_amount', 12, 2)->default(0)->comment('退款金额');
            // 退款状态：pending待处理/processing处理中/success成功/failed失败
            $table->string('refund_status', 30)->default('pending')->comment('退款状态');
            // 平台退款流水号
            $table->string('platform_refund_no', 100)->nullable()->comment('平台退款流水号');
            // 失败原因
            $table->string('failed_reason', 500)->nullable()->comment('失败原因');
            // 平台响应原始数据
            $table->json('platform_response')->nullable()->comment('平台响应数据(JSON)');
            $table->timestamps();

            // 租户+退款状态索引
            $table->index(['tenant_id', 'refund_status'], 'ix_71_2');
            // 租户+售后ID索引
            $table->index(['tenant_id', 'after_sale_id'], 'ix_71_3');
            // 订单ID索引
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_refund_records');
    }
};