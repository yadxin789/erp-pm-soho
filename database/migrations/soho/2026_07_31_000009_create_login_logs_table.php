<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 登录日志表迁移
 * 记录用户登录/登出事件，用于安全审计
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_login_logs', function (Blueprint $table) {
            $table->id();
            // 关联用户ID
            $table->foreignId('user_id')
                ->nullable()
                
                
                ->comment('登录用户ID');
            // 登录邮箱（用于记录登录尝试的邮箱）
            $table->string('email', 150)->nullable()->comment('登录邮箱');
            // 登录状态：success成功/failed失败
            $table->string('status', 20)->default('success')->comment('登录状态');
            // 来源IP地址
            $table->string('ip_address', 45)->comment('来源IP地址');
            // User-Agent
            $table->string('user_agent', 500)->nullable()->comment('浏览器User-Agent');
            // 浏览器名称
            $table->string('browser', 50)->nullable()->comment('浏览器');
            // 操作系统
            $table->string('os', 50)->nullable()->comment('操作系统');
            // 设备类型：desktop/mobile/tablet
            $table->string('device_type', 20)->nullable()->comment('设备类型');
            // 登录时间
            $table->timestamp('login_at')->useCurrent()->comment('登录时间');
            // 登出时间
            $table->timestamp('logout_at')->nullable()->comment('登出时间');
            // 会话ID
            $table->string('session_id', 100)->nullable()->comment('会话ID');
            // 登录方式：password密码/otp验证码/oauth第三方/token令牌
            $table->string('login_type', 30)->default('password')->comment('登录方式');
            // 备注
            $table->text('remark')->nullable()->comment('备注');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('login_at');
            $table->index(['user_id', 'status'], 'ix_94_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_login_logs');
    }
};