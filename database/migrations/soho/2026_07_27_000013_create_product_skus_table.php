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
        Schema::create('soho_product_skus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('product_id')->unsigned()->comment('商品ID(SPU)');
            $table->string('sku_code', 100)->comment('SKU编码');
            $table->string('barcode', 100)->nullable()->comment('条形码');
            $table->string('external_sku', 150)->nullable()->comment('外部SKU');
            $table->string('name', 255)->nullable()->comment('SKU名称');
            $table->json('specification')->nullable()->comment('规格信息');
            $table->decimal('cost_price', 18, 4)->default(0)->comment('成本价');
            $table->decimal('purchase_price', 18, 4)->default(0)->comment('采购价');
            $table->decimal('sale_price', 18, 4)->default(0)->comment('销售价');
            $table->decimal('weight', 18, 4)->nullable()->comment('重量');
            $table->decimal('length', 18, 4)->nullable()->comment('长度');
            $table->decimal('width', 18, 4)->nullable()->comment('宽度');
            $table->decimal('height', 18, 4)->nullable()->comment('高度');
            $table->integer('safety_quantity')->default(0)->comment('安全库存');
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            $table->unique(['tenant_id', 'sku_code'], 'ux_34_1');
            $table->index(['tenant_id', 'barcode'], 'ix_34_2');
            $table->index(['tenant_id', 'external_sku'], 'ix_34_3');
            $table->index(['product_id'], 'ix_34_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_skus');
    }
};