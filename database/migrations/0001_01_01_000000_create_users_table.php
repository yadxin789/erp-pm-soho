<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：users（三套并集 31 列）+ password_reset_tokens（主键 email）+ sessions
 * 依据：01-目标数据库字典 §2.1 / B-01 TC-02
 *
 * 说明：
 * - username 唯一键沿用 pm 设计，为 (username, deleted_at) 复合唯一（软删后允许同名重建）。
 * - email、phone 均可空且唯一；MySQL 唯一索引允许多个 NULL，符合"允许缺省"的要求。
 * - password 可空（三方合并后可能存在无密码账号，兼容外部认证）。
 * - department_id / position_id 的外键在 departments、positions 迁移中补齐（本表先建索引）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // 显示名；erp 现以 name 兼作登录名
            $table->string('name', 100);
            // 权威账号标识（pm）；erp/soho 迁移时回填
            $table->string('username', 50)->nullable();
            // 真实姓名（soho）
            $table->string('real_name', 50)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 30)->nullable()->unique();
            $table->string('password', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            // erp signature / pm bio 合并
            $table->string('signature', 500)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('position_id')->nullable()->index();
            // 1 启用 / 0 停用（pm is_active 折算）
            $table->tinyInteger('status')->default(1)->index();
            $table->boolean('is_admin')->default(false);
            // soho 语义，保留为模块私有概念（O3）
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            // D15 用户级模块归属：erp / pm / soho / hr / core
            $table->string('module', 20)->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('wecom_userid', 64)->nullable()->index();
            $table->timestamp('wecom_bound_at')->nullable();
            $table->boolean('wecom_notify_enabled')->default(true);
            $table->text('wecom_notify_settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['username', 'deleted_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // 统一以 email 为主键（05 文档 PM 影响）
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
