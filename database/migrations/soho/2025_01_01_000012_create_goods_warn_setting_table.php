<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_goods_warn_setting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_goods_id')->comment('系统商品ID');
            $table->integer('min_stock_threshold')->default(0)->comment('最低库存阈值');
            $table->tinyInteger('warn_enabled')->default(1)->comment('预警开关: 1开启 0关闭');
            $table->string('notify_users', 500)->nullable()->comment('通知人员(用户ID逗号分隔)');
            $table->timestamp('last_warn_at')->nullable()->comment('最后预警时间');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_goods_warn_setting');
    }
};
