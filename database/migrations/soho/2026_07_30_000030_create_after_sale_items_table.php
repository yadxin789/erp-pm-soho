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
        Schema::create('soho_after_sale_items', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 售后ID，关联售后主表
            $table->foreignId('after_sale_id')
                
                
                ->comment('售后ID');
            // 订单明细ID，关联订单明细
            $table->foreignId('order_item_id')
                ->nullable()
                
                
                ->comment('订单明细ID');
            // SKU ID，关联商品SKU
            $table->foreignId('sku_id')
                ->nullable()
                
                
                ->comment('SKU ID');
            // 售后申请数量
            $table->integer('quantity')->default(0)->comment('售后申请数量');
            // 已退款数量
            $table->integer('refund_quantity')->default(0)->comment('已退款数量');
            // 已退货数量
            $table->integer('return_quantity')->default(0)->comment('已退货数量');
            // 退款金额
            $table->decimal('refund_amount', 12, 2)->default(0)->comment('退款金额');
            // 退款状态：pending待处理/processing处理中/success已退款/failed退款失败
            $table->string('refund_status', 30)->default('pending')->comment('退款状态');
            $table->timestamps();

            // 租户+售后ID索引
            $table->index(['tenant_id', 'after_sale_id'], 'ix_70_1');
            // 租户+退款状态索引
            $table->index(['tenant_id', 'refund_status'], 'ix_70_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_after_sale_items');
    }
};