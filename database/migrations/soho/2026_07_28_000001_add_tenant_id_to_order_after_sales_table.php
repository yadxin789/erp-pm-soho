<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 为 order_after_sales 表添加 tenant_id 字段
 * 支持多租户隔离
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soho_order_after_sales', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                
                
                ->comment('租户ID');

            $table->index(['tenant_id', 'status'], 'ix_40_1');
        });
    }

    public function down(): void
    {
        Schema::table('soho_order_after_sales', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
