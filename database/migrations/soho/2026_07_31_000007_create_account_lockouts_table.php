<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 账号锁定记录表迁移
 * 记录账号锁定事件，包含锁定原因、锁定时间、解锁时间、解锁人等
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_account_lockouts', function (Blueprint $table) {
            $table->id();
            // 关联用户ID
            $table->foreignId('user_id')
                
                
                ->comment('被锁定的用户ID');
            // 锁定类型：manual手动/throttle自动/password_expired/password_wrong_too_many
            $table->string('lock_type', 30)->default('manual')->comment('锁定类型');
            // 锁定原因描述
            $table->text('reason')->nullable()->comment('锁定原因');
            // 锁定时关联的IP地址
            $table->string('ip_address', 45)->nullable()->comment('触发锁定的IP地址');
            // 锁定开始时间
            $table->timestamp('locked_at')->useCurrent()->comment('锁定时间');
            // 锁定持续时间（分钟）
            $table->unsignedInteger('lock_duration')->default(0)->comment('锁定时长（分钟，0为永久）');
            // 锁定结束时间（自动解锁时间）
            $table->timestamp('lock_expires_at')->nullable()->comment('锁定过期时间');
            // 解锁人ID
            $table->foreignId('unlocked_by')
                ->nullable()
                
                
                ->comment('解锁人ID');
            // 解锁时间
            $table->timestamp('unlocked_at')->nullable()->comment('解锁时间');
            // 解锁方式：manual手动/time_expired自动过期/password_reset密码重置
            $table->string('unlock_type', 30)->nullable()->comment('解锁方式');
            // 解锁备注
            $table->text('unlock_note')->nullable()->comment('解锁备注');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('user_id');
            $table->index('locked_at');
            $table->index('lock_expires_at');
            $table->index(['user_id', 'unlocked_at'], 'ix_92_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_account_lockouts');
    }
};