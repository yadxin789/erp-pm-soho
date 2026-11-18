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
        Schema::create('soho_inventory_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('inventory_id')->unsigned()->comment('库存ID');
            $table->bigInteger('sku_id')->unsigned()->comment('SKU ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->string('type', 40)->comment('变动类型：sale_out(销售出库), purchase_in(采购入库), return_in(退货入库), transfer_out(调拨出库), transfer_in(调拨入库), stock_take(盘点), manual_adjust(手工调整), platform_sync(平台同步修正), init(库存初始化), lock(锁定), unlock(解锁)');
            $table->integer('before_quantity')->comment('变动前数量');
            $table->integer('change_quantity')->comment('变动数量');
            $table->integer('after_quantity')->comment('变动后数量');
            $table->string('reference_type', 50)->nullable()->comment('业务类型');
            $table->string('reference_no', 100)->nullable()->comment('业务单号');
            $table->bigInteger('operator_id')->unsigned()->nullable()->comment('操作人ID');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['inventory_id', 'created_at'], 'ix_25_1');
            $table->index(['reference_type', 'reference_no'], 'ix_25_2');
            $table->index(['tenant_id', 'sku_id'], 'ix_25_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_transactions');
    }
};