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
        Schema::create('soho_platform_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('shop_id')->unsigned()->comment('店铺ID');
            $table->bigInteger('product_id')->unsigned()->nullable()->comment('关联内部商品ID');
            $table->string('platform_product_id', 150)->comment('平台商品ID');
            $table->string('platform_category_id', 150)->nullable()->comment('平台类目ID');
            $table->string('title', 255)->nullable()->comment('平台商品标题');
            $table->string('platform_status', 50)->nullable()->comment('平台状态');
            $table->json('raw_data')->nullable()->comment('平台原始数据');
            $table->timestamp('last_synced_at')->nullable()->comment('最近同步时间');
            $table->timestamps();

            $table->unique(['shop_id', 'platform_product_id'], 'ux_38_1');
            $table->index(['tenant_id', 'product_id'], 'ix_38_2');
            $table->index(['shop_id', 'platform_status'], 'ix_38_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_platform_products');
    }
};