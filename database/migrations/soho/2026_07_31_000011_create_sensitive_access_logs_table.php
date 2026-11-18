<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 敏感数据访问日志表迁移
 * 记录对敏感数据（如财务、客户信息）的访问行为
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_sensitive_access_logs', function (Blueprint $table) {
            $table->id();
            // 访问用户ID
            $table->foreignId('user_id')
                ->nullable()
                
                
                ->comment('访问用户ID');
            // 数据类型：customer客户/finance财务/order订单/product商品
            $table->string('data_type', 50)->comment('数据类型');
            // 操作类型：view查看/export导出/edit编辑/delete删除
            $table->string('action', 30)->comment('操作类型');
            // 关联记录ID
            $table->unsignedBigInteger('record_id')->nullable()->comment('关联记录ID');
            // 记录标识（如订单号、客户名）
            $table->string('record_identifier', 200)->nullable()->comment('记录标识');
            // 访问IP
            $table->string('ip_address', 45)->nullable()->comment('访问IP');
            // User-Agent
            $table->string('user_agent', 500)->nullable()->comment('User-Agent');
            // 访问时间
            $table->timestamp('accessed_at')->useCurrent()->comment('访问时间');
            // 备注
            $table->text('remark')->nullable()->comment('备注');

            $table->index('user_id');
            $table->index('data_type');
            $table->index('accessed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_sensitive_access_logs');
    }
};