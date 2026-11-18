<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_sync_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task_name', 200)->comment('任务名称');
            $table->string('task_type', 30)->comment('任务类型: order_sync订单同步/stock_sync库存同步/goods_publish商品发布/ship_push发货推送/token_refresh令牌刷新');
            $table->string('platform', 30)->nullable()->comment('平台');
            $table->foreignId('shop_id')->nullable()->comment('店铺ID');
            $table->string('status', 30)->default('running')->comment('状态: running运行中/success成功/failed失败');
            $table->integer('total_count')->default(0)->comment('处理总数');
            $table->integer('success_count')->default(0)->comment('成功数');
            $table->integer('fail_count')->default(0)->comment('失败数');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->json('extra_data')->nullable()->comment('额外数据(JSON)');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('finished_at')->nullable()->comment('完成时间');
            $table->timestamps();

            $table->index(['task_type', 'status'], 'ix_18_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_sync_task_logs');
    }
};
