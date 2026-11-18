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
        Schema::create('soho_domain_events', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 事件类型：order.created/order.paid/product.created/stock.adjusted等
            $table->string('event_type', 100)->comment('事件类型');
            // 聚合根类型：order订单/product商品/inventory库存等
            $table->string('aggregate_type', 50)->comment('聚合根类型');
            // 聚合根ID
            $table->string('aggregate_id', 100)->comment('聚合根ID');
            // 事件数据JSON
            $table->json('data')->nullable()->comment('事件数据(JSON)');
            // 事件发生时间
            $table->timestamp('occurred_at')->comment('事件发生时间');
            // 事件发布时间
            $table->timestamp('published_at')->nullable()->comment('事件发布时间');
            // 发布状态：pending待发布/publishing发布中/published已发布/failed发布失败
            $table->string('publish_status', 20)->default('pending')->comment('发布状态');
            // 重试次数
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('重试次数');
            $table->timestamps();

            // 租户+聚合类型+聚合根ID索引
            $table->index(['tenant_id', 'aggregate_type', 'aggregate_id'], 'ix_84_1');
            // 租户+事件类型索引
            $table->index(['tenant_id', 'event_type'], 'ix_84_2');
            // 租户+发布状态索引
            $table->index(['tenant_id', 'publish_status'], 'ix_84_3');
            // 事件发生时间索引
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_domain_events');
    }
};
