<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_system_goods', function (Blueprint $table) {
            $table->id();
            $table->string('goods_name', 300)->comment('商品名称');
            $table->string('system_sku', 100)->unique('ux_8_1')->comment('系统统一SKU');
            $table->string('barcode', 100)->nullable()->comment('商品条码');
            $table->decimal('cost_price', 12, 2)->default(0)->comment('成本价');
            $table->decimal('selling_price', 12, 2)->default(0)->comment('销售价');
            $table->integer('system_stock')->default(0)->comment('系统库存');
            $table->json('images')->nullable()->comment('商品图片(JSON数组)');
            $table->text('description')->nullable()->comment('商品详情');
            $table->string('category', 100)->nullable()->comment('商品分类');
            $table->string('brand', 100)->nullable()->comment('品牌');
            $table->string('unit', 20)->default('件')->comment('单位');
            $table->tinyInteger('status')->default(1)->comment('状态: 1上架 0下架');
            $table->integer('min_stock_warn')->default(0)->comment('最低库存预警阈值');
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_system_goods');
    }
};
