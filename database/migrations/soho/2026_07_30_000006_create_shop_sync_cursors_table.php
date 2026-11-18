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
        Schema::create('soho_shop_sync_cursors', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 店铺ID
            $table->foreignId('shop_id')
                
                
                ->comment('店铺ID');
            // 平台编码（冗余，便于查询）
            $table->string('platform_code', 50)->comment('平台编码');
            // 同步类型：product商品/order订单/inventory库存/shipment物流/after_sale售后/settlement结算/customer客户/promotion促销
            $table->string('sync_type', 50)->comment('同步类型');
            // 同步模式：webhook webhook触发/scheduled定时任务/manual手动
            $table->string('sync_mode', 30)->default('scheduled')->comment('同步模式');
            // 当前游标值（时间戳或ID，取决于平台）
            $table->string('cursor', 100)->comment('当前游标值');
            // 游标类型：timestamp时间戳/id自增ID/sequential顺序号
            $table->string('cursor_type', 30)->default('timestamp')->comment('游标类型');
            // 上次同步开始时间
            $table->timestamp('last_sync_start_at')->nullable()->comment('上次同步开始时间');
            // 上次同步结束时间
            $table->timestamp('last_sync_end_at')->nullable()->comment('上次同步结束时间');
            // 上次成功同步时间
            $table->timestamp('last_success_at')->nullable()->comment('上次成功同步时间');
            // 上次失败时间
            $table->timestamp('last_failure_at')->nullable()->comment('上次失败时间');
            // 上次同步耗时（秒）
            $table->unsignedInteger('last_sync_duration')->nullable()->comment('上次同步耗时(秒)');
            // 重叠时间窗口（分钟），用于防止数据遗漏
            $table->unsignedInteger('overlap_window')->default(30)->comment('重叠时间窗口(分钟)');
            // 下次同步时间
            $table->timestamp('next_sync_at')->nullable()->comment('下次同步时间');
            // 同步间隔（分钟）
            $table->unsignedInteger('sync_interval')->default(60)->comment('同步间隔(分钟)');
            // 批量大小
            $table->unsignedInteger('batch_size')->default(100)->comment('批量大小');
            // 最大重试次数
            $table->unsignedInteger('max_retries')->default(3)->comment('最大重试次数');
            // 当前重试次数
            $table->unsignedInteger('retry_count')->default(0)->comment('当前重试次数');
            // 连续失败次数
            $table->unsignedInteger('consecutive_failures')->default(0)->comment('连续失败次数');
            // 最后错误信息
            $table->text('last_error_message')->nullable()->comment('最后错误信息');
            // 最后错误代码
            $table->string('last_error_code', 50)->nullable()->comment('最后错误代码');
            // 最后错误详情JSON
            $table->json('last_error_detail')->nullable()->comment('最后错误详情(JSON)');
            // 本次同步拉取条数
            $table->unsignedInteger('last_fetched_count')->default(0)->comment('本次同步拉取条数');
            // 累计同步条数
            $table->unsignedBigInteger('total_synced_count')->default(0)->comment('累计同步条数');
            // 累计成功次数
            $table->unsignedBigInteger('total_success_count')->default(0)->comment('累计成功次数');
            // 累计失败次数
            $table->unsignedBigInteger('total_failure_count')->default(0)->comment('累计失败次数');
            // 分页断点信息JSON
            $table->json('pagination_state')->nullable()->comment('分页断点信息(JSON)');
            // 自定义配置JSON
            $table->json('config')->nullable()->comment('自定义配置(JSON)');
            // 状态：active同步中/idle空闲/paused已暂停/error错误
            $table->string('status', 20)->default('idle')->comment('状态');
            // 是否启用
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            // 创建人
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人ID');
            $table->timestamps();

            // 租户+店铺+同步类型唯一索引
            $table->unique(['tenant_id', 'shop_id', 'sync_type'], 'ux_62_1');
            // 租户+平台编码索引
            $table->index(['tenant_id', 'platform_code'], 'ix_62_2');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_62_3');
            // 下次同步时间索引
            $table->index(['tenant_id', 'next_sync_at'], 'ix_62_4');
            // 租户+启用状态索引
            $table->index(['tenant_id', 'is_enabled'], 'ix_62_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_shop_sync_cursors');
    }
};