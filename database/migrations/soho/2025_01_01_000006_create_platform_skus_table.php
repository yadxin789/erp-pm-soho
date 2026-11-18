<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_platform_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_goods_id')->comment('系统商品ID');
            $table->foreignId('shop_id')->comment('店铺ID');
            $table->string('platform_sku', 200)->comment('平台SKU编码');
            $table->string('platform_goods_id', 200)->nullable()->comment('平台商品ID');
            $table->string('platform_goods_url', 500)->nullable()->comment('平台商品链接');
            $table->decimal('platform_price', 12, 2)->nullable()->comment('平台售价');
            $table->integer('platform_stock')->nullable()->comment('平台当前库存');
            $table->tinyInteger('stock_sync_enabled')->default(1)->comment('库存同步开关: 1开启 0关闭');
            $table->tinyInteger('sync_status')->default(0)->comment('同步状态: 0未同步 1已同步 2同步失败');
            $table->timestamp('last_sync_at')->nullable()->comment('最后同步时间');
            $table->text('sync_error')->nullable()->comment('同步错误信息');
            $table->timestamps();

            $table->unique(['system_goods_id', 'shop_id'], 'ux_9_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_platform_skus');
    }
};
