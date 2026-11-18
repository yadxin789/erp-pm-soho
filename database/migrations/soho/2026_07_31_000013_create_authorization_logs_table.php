<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 授权操作日志表迁移
 * 记录所有授权操作历史，包括授权、重新授权、撤销授权等操作
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_authorization_logs', function (Blueprint $table) {
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
            // 操作类型：authorize授权/reauthorize重新授权/revoke撤销/refresh刷新/token_refresh令牌刷新
            $table->string('operation_type', 30)->comment('操作类型');
            // 操作对象类型：shop店铺/platform_account平台账号
            $table->string('target_type', 30)->comment('操作对象类型');
            // 操作对象ID
            $table->unsignedBigInteger('target_id')->nullable()->comment('操作对象ID');
            // 操作人ID
            $table->foreignId('operator_id')
                ->nullable()
                
                
                ->comment('操作人ID');
            // 操作人姓名（冗余字段，便于查询）
            $table->string('operator_name', 100)->nullable()->comment('操作人姓名');
            // 操作结果：success成功/failed失败
            $table->string('result', 20)->default('success')->comment('操作结果');
            // 操作时间
            $table->timestamp('operated_at')->useCurrent()->comment('操作时间');
            // 操作IP地址
            $table->string('ip_address', 45)->nullable()->comment('操作IP地址');
            // User-Agent
            $table->string('user_agent', 500)->nullable()->comment('浏览器User-Agent');
            // 操作详情描述
            $table->text('detail')->nullable()->comment('操作详情');
            // 错误信息（如果操作失败）
            $table->text('error_message')->nullable()->comment('错误信息');
            // 额外数据（JSON格式）
            $table->json('extra_data')->nullable()->comment('额外数据');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('shop_id');
            $table->index('platform_account_id');
            $table->index('operation_type');
            $table->index('result');
            $table->index('operated_at');
            $table->index('operator_id');
            // 复合索引：租户+操作类型+时间
            $table->index(['tenant_id', 'operation_type', 'operated_at'], 'ix_98_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_authorization_logs');
    }
};