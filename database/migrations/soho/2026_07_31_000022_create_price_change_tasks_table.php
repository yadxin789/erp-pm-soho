<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 价格调价任务表迁移
 * 记录具体的价格调整执行任务，支持手动、批量、自动三种任务类型，
 * 可针对商品、SKU或店铺执行价格更新
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_price_change_tasks', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 关联调价申请ID
            $table->foreignId('request_id')
                ->nullable()
                
                
                ->comment('关联调价申请');
            // 任务类型：manual手动/batch批量/auto自动
            $table->string('task_type', 30)->comment('任务类型:manual手动/batch批量/auto自动');
            // 目标类型：product商品/sku SKU/shop店铺
            $table->string('target_type', 30)->comment('目标类型:product商品/sku SKU/shop店铺');
            // 目标ID
            $table->unsignedBigInteger('target_id')->comment('目标ID');
            // 目标编码（冗余字段，便于查询）
            $table->string('target_code', 100)->nullable()->comment('目标编码');
            // 目标价格
            $table->decimal('target_price', 12, 2)->comment('目标价格');
            // 原始价格
            $table->decimal('original_price', 12, 2)->nullable()->comment('原始价格');
            // 状态：pending待执行/processing执行中/completed已完成/failed失败/cancelled已取消
            $table->string('status', 30)->default('pending')->comment('状态:pending待执行/processing执行中/completed已完成/failed失败/cancelled已取消');
            // 计划执行时间
            $table->timestamp('scheduled_at')->nullable()->comment('计划执行时间');
            // 实际执行时间
            $table->timestamp('executed_at')->nullable()->comment('实际执行时间');
            // 执行人ID
            $table->foreignId('executed_by')
                ->nullable()
                
                
                ->comment('执行人');
            // 执行结果描述
            $table->text('result_message')->nullable()->comment('执行结果');
            // 执行结果数据（JSON格式），存储详细执行结果
            $table->json('result_data')->nullable()->comment('执行结果数据');
            // 创建时间和更新时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('request_id');
            $table->index('status');
            $table->index('target_type');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_price_change_tasks');
    }
};
