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
        Schema::create('soho_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->bigInteger('sku_id')->unsigned()->nullable()->comment('内部SKU ID');
            $table->string('platform_sku_id', 150)->nullable()->comment('平台SKU ID');
            $table->string('product_name', 500)->comment('商品名称');
            $table->string('sku_name', 255)->nullable()->comment('SKU名称');
            $table->integer('quantity')->default(1)->comment('数量');
            $table->decimal('unit_price', 18, 4)->default(0)->comment('单价');
            $table->decimal('discount_amount', 18, 4)->default(0)->comment('优惠金额');
            $table->decimal('refund_amount', 18, 4)->default(0)->comment('退款金额');
            $table->decimal('cost_price', 18, 4)->default(0)->comment('成本价');
            $table->decimal('profit_amount', 18, 4)->nullable()->comment('利润');
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->comment('仓库ID');
            $table->string('fulfillment_status', 30)->default('pending')->comment('履约状态：pending(待履约), picking(拣货中), packed(已打包), shipped(已发货), cancelled(已取消)');
            $table->json('raw_data')->nullable()->comment('平台原始数据');
            $table->timestamps();

            $table->index(['order_id'], 'ix_26_1');
            $table->index(['sku_id'], 'ix_26_2');
            $table->index(['tenant_id', 'order_id'], 'ix_26_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_order_items');
    }
};