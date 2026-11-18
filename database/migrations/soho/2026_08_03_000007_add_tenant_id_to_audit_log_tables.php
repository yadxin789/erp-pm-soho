<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 为 operation_logs 和 sensitive_access_logs 添加 tenant_id 字段
 * 审计报表控制器需要按租户隔离查询这两个表
 */
return new class extends Migration
{
    public function up(): void
    {
        // 为 operation_logs 添加 tenant_id
        Schema::table('soho_operation_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                
                
                ->comment('租户ID');
            $table->index(['tenant_id', 'created_at'], 'ix_108_1');
        });

        // 为 sensitive_access_logs 添加 tenant_id
        Schema::table('soho_sensitive_access_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                
                
                ->comment('租户ID');
            $table->index(['tenant_id', 'accessed_at'], 'ix_108_2');
        });
    }

    public function down(): void
    {
        Schema::table('soho_sensitive_access_logs', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'accessed_at']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('soho_operation_logs', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropColumn('tenant_id');
        });
    }
};
