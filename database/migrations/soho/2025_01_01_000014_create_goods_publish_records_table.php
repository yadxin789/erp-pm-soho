<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_goods_publish_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_goods_id')->comment('系统商品ID');
            $table->foreignId('shop_id')->comment('店铺ID');
            $table->string('platform', 30)->comment('平台');
            $table->string('publish_status', 30)->default('pending')->comment('发布状态: pending待发布/publishing发布中/success成功/failed失败');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->string('platform_goods_id', 200)->nullable()->comment('发布后平台商品ID');
            $table->foreignId('operator_id')->nullable()->comment('操作人');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_goods_publish_records');
    }
};
