<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_stock_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_goods_id')->comment('系统商品ID');
            $table->foreignId('shop_id')->nullable()->comment('店铺ID');
            $table->string('platform', 30)->nullable()->comment('平台');
            $table->integer('before_stock')->default(0)->comment('同步前库存');
            $table->integer('after_stock')->default(0)->comment('同步后库存');
            $table->string('sync_type', 30)->comment('同步类型: push主动推送/pull被动拉取/manual手动同步');
            $table->string('sync_status', 30)->default('success')->comment('同步状态: success成功/failed失败');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->foreignId('operator_id')->nullable()->comment('操作人(手动同步时记录)');
            $table->timestamps();

            $table->index(['system_goods_id', 'shop_id'], 'ix_14_1');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_stock_sync_logs');
    }
};
