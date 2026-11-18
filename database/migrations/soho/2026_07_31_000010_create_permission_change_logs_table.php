<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 权限变更日志表迁移
 * 记录用户角色、权限的变更历史
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_permission_change_logs', function (Blueprint $table) {
            $table->id();
            // 变更类型：role_assign角色分配/role_remove角色移除/permission_assign权限分配/permission_remove权限移除
            $table->string('change_type', 50)->comment('变更类型');
            // 目标用户ID（被变更权限的用户）
            $table->foreignId('target_user_id')
                
                
                ->comment('目标用户ID');
            // 操作人ID（执行变更的用户）
            $table->foreignId('operator_id')
                ->nullable()
                
                
                ->comment('操作人ID');
            // 关联角色ID（如果是角色变更）
            $table->foreignId('role_id')
                ->nullable()
                
                
                ->comment('关联角色ID');
            // 关联权限ID（如果是权限变更）
            $table->foreignId('permission_id')
                ->nullable()
                
                
                ->comment('关联权限ID');
            // 变更前值（JSON）
            $table->json('before_value')->nullable()->comment('变更前值');
            // 变更后值（JSON）
            $table->json('after_value')->nullable()->comment('变更后值');
            // 变更描述
            $table->string('description', 500)->nullable()->comment('变更描述');
            // 操作IP
            $table->string('ip_address', 45)->nullable()->comment('操作IP');
            // 创建时间
            $table->timestamp('created_at')->useCurrent()->comment('变更时间');

            // 索引
            $table->index('target_user_id');
            $table->index('operator_id');
            $table->index('change_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_permission_change_logs');
    }
};