<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 登录失败记录表迁移
 * 记录所有登录失败尝试，用于安全审计和暴力破解检测
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_login_failures', function (Blueprint $table) {
            $table->id();
            // 关联用户ID（如果尝试登录已存在账户）
            $table->foreignId('user_id')
                ->nullable()
                
                
                ->comment('尝试登录的用户ID');
            // 尝试登录的邮箱
            $table->string('email', 150)->nullable()->comment('尝试登录的邮箱');
            // 来源IP地址
            $table->string('ip_address', 45)->comment('来源IP地址');
            // User-Agent
            $table->string('user_agent', 500)->nullable()->comment('浏览器User-Agent');
            // 失败原因：invalid_password/password_expired/account_locked/account_not_found/throttle
            $table->string('failure_reason', 50)->default('invalid_password')->comment('失败原因');
            // 失败详情描述
            $table->text('detail')->nullable()->comment('失败详情');
            // 是否已触发锁定
            $table->boolean('triggered_lockout')->default(false)->comment('是否已触发锁定');
            // 创建时间
            $table->timestamp('created_at')->useCurrent()->comment('失败时间');

            // 索引
            $table->index('email');
            $table->index('ip_address');
            $table->index('user_id');
            $table->index('created_at');
            // IP+时间组合索引（用于速率限制检测）
            $table->index(['ip_address', 'created_at'], 'ix_91_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_login_failures');
    }
};