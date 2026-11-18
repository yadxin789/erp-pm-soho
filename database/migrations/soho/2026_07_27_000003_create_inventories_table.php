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
        Schema::create('soho_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->bigInteger('sku_id')->unsigned()->comment('SKU ID');
            $table->integer('actual_quantity')->default(0)->comment('实际库存');
            $table->integer('locked_quantity')->default(0)->comment('锁定库存');
            $table->integer('inbound_quantity')->default(0)->comment('待入库库存');
            $table->integer('in_transit_quantity')->default(0)->comment('在途库存');
            $table->integer('damaged_quantity')->default(0)->comment('不良品库存');
            $table->integer('return_pending_quantity')->default(0)->comment('退货待检库存');
            $table->integer('safety_quantity')->default(0)->comment('安全库存');
            $table->integer('version')->default(0)->comment('乐观锁版本号');
            $table->timestamp('last_count_at')->nullable()->comment('最近盘点时间');
            $table->timestamps();

            $table->unique(['warehouse_id', 'sku_id'], 'ux_24_1');
            $table->index(['tenant_id', 'sku_id'], 'ix_24_2');
            $table->index(['tenant_id', 'warehouse_id'], 'ix_24_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inventories');
    }
};