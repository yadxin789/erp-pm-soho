<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token刷新记录表迁移
 * 记录所有Token刷新历史，用于监控Token生命周期和排查刷新问题
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_token_refresh_logs', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 关联店铺ID（可选）
            $table->foreignId('shop_id')
                ->nullable()
                
                
                ->comment('店铺ID');
            // 关联平台账号ID（可选）
            $table->foreignId('platform_account_id')
                ->nullable()
                
                
                ->comment('平台账号ID');
            // 刷新对象类型：shop店铺/platform_account平台账号
            $table->string('target_type', 30)->comment('刷新对象类型');
            // 刷新对象ID
            $table->unsignedBigInteger('target_id')->nullable()->comment('刷新对象ID');
            // 平台名称
            $table->string('platform', 50)->nullable()->comment('平台名称');
            // 刷新结果：success成功/failed失败
            $table->string('result', 20)->default('success')->comment('刷新结果');
            // 刷新时间
            $table->timestamp('refreshed_at')->useCurrent()->comment('刷新时间');
            // 刷新方式：auto自动/manual手动/scheduled定时
            $table->string('refresh_method', 20)->default('auto')->comment('刷新方式');
            // 操作人ID（手动刷新时）
            $table->foreignId('operator_id')
                ->nullable()
                
                
                ->comment('操作人ID');
            // 旧Token过期时间
            $table->timestamp('old_token_expires_at')->nullable()->comment('旧Token过期时间');
            // 新Token过期时间
            $table->timestamp('new_token_expires_at')->nullable()->comment('新Token过期时间');
            // Token有效期（秒）
            $table->unsignedInteger('token_lifetime_seconds')->nullable()->comment('Token有效期(秒)');
            // 刷新前剩余有效时间（秒）
            $table->unsignedInteger('remaining_seconds')->nullable()->comment('刷新前剩余有效时间(秒)');
            // 错误信息（如果刷新失败）
            $table->text('error_message')->nullable()->comment('错误信息');
            // 错误代码（平台返回）
            $table->string('error_code', 100)->nullable()->comment('错误代码');
            // 请求IP地址
            $table->string('ip_address', 45)->nullable()->comment('请求IP地址');
            // 额外数据（JSON格式）
            $table->json('extra_data')->nullable()->comment('额外数据');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('shop_id');
            $table->index('platform_account_id');
            $table->index('result');
            $table->index('refreshed_at');
            $table->index('refresh_method');
            // 复合索引：租户+结果+时间
            $table->index(['tenant_id', 'result', 'refreshed_at'], 'ix_100_1');
            // 复合索引：对象类型+对象ID+时间
            $table->index(['target_type', 'target_id', 'refreshed_at'], 'ix_100_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_token_refresh_logs');
    }
};