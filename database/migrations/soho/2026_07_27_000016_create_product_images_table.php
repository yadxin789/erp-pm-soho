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
        Schema::create('soho_product_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('product_id')->unsigned()->comment('商品ID');
            $table->bigInteger('sku_id')->unsigned()->nullable()->comment('SKU ID');
            $table->string('image_path', 500)->comment('图片路径');
            $table->string('image_url', 500)->nullable()->comment('图片URL');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_main')->default(false)->comment('是否主图');
            $table->string('alt_text', 200)->nullable()->comment('替代文本');
            $table->timestamps();

            $table->index(['product_id'], 'ix_37_1');
            $table->index(['sku_id'], 'ix_37_2');
            $table->index(['tenant_id', 'product_id'], 'ix_37_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_images');
    }
};