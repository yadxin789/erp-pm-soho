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
        Schema::create('soho_integration_outbox', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 目标平台：taobao淘宝/jd京东/amazon亚马逊/pdd拼多多等
            $table->string('target_platform', 50)->comment('目标平台');
            // 事件类型：order.created/order.updated/product.created/product.updated等
            $table->string('event_type', 100)->comment('事件类型');
            // 事件ID（用于幂等去重）
            $table->string('event_id', 100)->comment('事件ID');
            // 事件载荷JSON
            $table->json('payload')->nullable()->comment('事件载荷(JSON)');
            // 请求头信息JSON
            $table->json('headers')->nullable()->comment('请求头信息(JSON)');
            // 发送时间
            $table->timestamp('sent_at')->nullable()->comment('发送时间');
            // 发送状态：pending待发送/sending发送中/sent已发送/failed失败
            $table->string('send_status', 20)->default('pending')->comment('发送状态');
            // 重试次数
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('重试次数');
            // 下次重试时间
            $table->timestamp('next_retry_at')->nullable()->comment('下次重试时间');
            $table->timestamps();

            // 租户+目标平台+事件ID唯一索引（幂等去重）
            $table->unique(['tenant_id', 'target_platform', 'event_id'], 'ux_83_1');
            // 租户+事件类型索引
            $table->index(['tenant_id', 'event_type'], 'ix_83_2');
            // 租户+发送状态索引
            $table->index(['tenant_id', 'send_status'], 'ix_83_3');
            // 下次重试时间索引（用于批量重试）
            $table->index('next_retry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_integration_outbox');
    }
};
