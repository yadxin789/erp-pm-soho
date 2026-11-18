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
        Schema::create('soho_inventory_reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->bigInteger('sku_id')->unsigned()->comment('SKU ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->bigInteger('order_item_id')->unsigned()->nullable()->comment('订单明细ID');
            $table->integer('quantity')->comment('预占数量');
            $table->integer('released_quantity')->default(0)->comment('已释放数量');
            $table->string('status', 30)->default('reserved')->comment('状态: reserved已预占/partially_released部分释放/released已释放');
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['tenant_id', 'warehouse_id'], 'ix_67_1');
            $table->index(['tenant_id', 'sku_id'], 'ix_67_2');
            $table->index(['tenant_id', 'order_id'], 'ix_67_3');
            $table->index(['tenant_id', 'status'], 'ix_67_4');
            $table->index(['tenant_id', 'expires_at'], 'ix_67_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_reservations');
    }
};