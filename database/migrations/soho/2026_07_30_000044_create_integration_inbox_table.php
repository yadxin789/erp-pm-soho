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
        Schema::create('soho_integration_inbox', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 来源平台：taobao淘宝/jd京东/amazon亚马逊/pdd拼多多等
            $table->string('source_platform', 50)->comment('来源平台');
            // 事件类型：order.created/order.updated/order.cancelled/product.created/product.updated等
            $table->string('event_type', 100)->comment('事件类型');
            // 平台事件ID（用于幂等去重）
            $table->string('event_id', 100)->comment('平台事件ID');
            // 事件载荷JSON
            $table->json('payload')->nullable()->comment('事件载荷(JSON)');
            // 请求头信息JSON
            $table->json('headers')->nullable()->comment('请求头信息(JSON)');
            // 处理时间
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            // 处理状态：pending待处理/processing处理中/completed已处理/failed失败/ignored已忽略
            $table->string('process_status', 20)->default('pending')->comment('处理状态');
            // 重试次数
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('重试次数');
            // 下次重试时间
            $table->timestamp('next_retry_at')->nullable()->comment('下次重试时间');
            $table->timestamps();

            // 租户+来源平台+事件ID唯一索引（幂等去重）
            $table->unique(['tenant_id', 'source_platform', 'event_id'], 'ux_82_1');
            // 租户+事件类型索引
            $table->index(['tenant_id', 'event_type'], 'ix_82_2');
            // 租户+处理状态索引
            $table->index(['tenant_id', 'process_status'], 'ix_82_3');
            // 下次重试时间索引（用于批量重试）
            $table->index('next_retry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_integration_inbox');
    }
};
