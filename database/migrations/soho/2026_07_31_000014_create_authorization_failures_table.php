<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 授权失败记录表迁移
 * 记录所有授权失败事件，用于问题排查和告警
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_authorization_failures', function (Blueprint $table) {
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
            // 失败类型：authorize授权失败/refresh刷新失败/revoke撤销失败/token_expired令牌过期/network网络错误/permission权限不足/rate_limit限流/invalid_code无效授权码
            $table->string('failure_type', 50)->comment('失败类型');
            // 失败对象类型：shop店铺/platform_account平台账号
            $table->string('target_type', 30)->comment('失败对象类型');
            // 失败对象ID
            $table->unsignedBigInteger('target_id')->nullable()->comment('失败对象ID');
            // 失败原因代码（平台返回的错误码）
            $table->string('error_code', 100)->nullable()->comment('失败原因代码');
            // 失败详情描述
            $table->text('error_message')->nullable()->comment('失败详情');
            // 平台名称
            $table->string('platform', 50)->nullable()->comment('平台名称');
            // 失败时间
            $table->timestamp('failed_at')->useCurrent()->comment('失败时间');
            // 重试次数
            $table->unsignedInteger('retry_count')->default(0)->comment('重试次数');
            // 最大重试次数
            $table->unsignedInteger('max_retries')->default(3)->comment('最大重试次数');
            // 解决状态：pending待处理/resolving处理中/resolved已解决/ignored已忽略
            $table->string('status', 20)->default('pending')->comment('解决状态');
            // 解决时间
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            // 解决人ID
            $table->foreignId('resolved_by')
                ->nullable()
                
                
                ->comment('解决人ID');
            // 解决备注
            $table->text('resolution_note')->nullable()->comment('解决备注');
            // 请求参数（JSON格式，脱敏）
            $table->json('request_params')->nullable()->comment('请求参数');
            // 响应数据（JSON格式，脱敏）
            $table->json('response_data')->nullable()->comment('响应数据');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('shop_id');
            $table->index('platform_account_id');
            $table->index('failure_type');
            $table->index('status');
            $table->index('failed_at');
            // 复合索引：租户+状态+时间
            $table->index(['tenant_id', 'status', 'failed_at'], 'ix_99_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_authorization_failures');
    }
};