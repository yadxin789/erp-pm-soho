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
        Schema::create('soho_inventory_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->bigInteger('sku_id')->unsigned()->comment('SKU ID');
            $table->string('batch_no', 100)->comment('批次号');
            $table->integer('quantity')->comment('批次数量');
            $table->integer('available_quantity')->default(0)->comment('可用数量');
            $table->decimal('unit_cost', 18, 4)->default(0)->comment('单位成本');
            $table->decimal('total_cost', 18, 4)->default(0)->comment('总成本');
            $table->date('production_date')->nullable()->comment('生产日期');
            $table->date('expiry_date')->nullable()->comment('过期日期');
            $table->bigInteger('supplier_id')->unsigned()->nullable()->comment('供应商ID');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['tenant_id', 'warehouse_id'], 'ix_68_1');
            $table->index(['tenant_id', 'sku_id'], 'ix_68_2');
            $table->index(['tenant_id', 'batch_no'], 'ix_68_3');
            $table->index(['tenant_id', 'expiry_date'], 'ix_68_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_batches');
    }
};