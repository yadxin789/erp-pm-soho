<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_platform_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->comment('平台');
            $table->foreignId('shop_id')->nullable()->comment('店铺ID');
            $table->string('api_method', 200)->comment('调用的API方法');
            $table->string('request_url', 1000)->nullable()->comment('请求URL');
            $table->json('request_params')->nullable()->comment('请求参数(JSON)');
            $table->json('response_data')->nullable()->comment('响应数据(JSON)');
            $table->string('response_status', 30)->default('success')->comment('响应状态: success成功/failed失败/timeout超时');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->integer('response_time_ms')->default(0)->comment('响应时间(毫秒)');
            $table->string('trace_id', 100)->nullable()->comment('链路追踪ID');
            $table->string('ip_address', 45)->nullable()->comment('请求IP');
            $table->timestamps();

            $table->index(['platform', 'shop_id'], 'ix_16_1');
            $table->index('response_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_platform_api_logs');
    }
};
