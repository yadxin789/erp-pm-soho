<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IP 白名单表迁移
 * 管理允许访问系统的 IP 地址列表
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_ip_whitelists', function (Blueprint $table) {
            $table->id();
            // IP地址或网段（支持CIDR格式，如192.168.1.0/24）
            $table->string('ip_address', 50)->comment('IP地址或网段');
            // 描述说明
            $table->string('description', 255)->nullable()->comment('描述说明');
            // 类型：allow白名单/block黑名单
            $table->enum('type', ['allow', 'block'])->default('allow')->comment('类型');
            // 适用范围：admin管理后台/api接口/all全部
            $table->string('scope', 30)->default('all')->comment('适用范围');
            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');
            // 创建人ID
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人ID');
            $table->timestamps();

            // 索引
            $table->index('ip_address');
            $table->index(['type', 'is_active'], 'ix_93_1');
            $table->unique(['ip_address', 'scope'], 'ux_93_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_ip_whitelists');
    }
};