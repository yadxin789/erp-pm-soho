<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 授权异常表迁移
 * 记录所有授权相关异常，包括异常类型、详情、处理状态等
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_authorization_exceptions', function (Blueprint $table) {
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
            // 异常类型：token_expired令牌过期/token_invalid令牌无效/permission_denied权限被拒/auth_failed授权失败/rate_limit_exceeded限流超限/api_error接口错误/callback_failed回调失败/network_error网络错误/other其他
            $table->string('exception_type', 50)->comment('异常类型');
            // 异常对象类型：shop店铺/platform_account平台账号
            $table->string('target_type', 30)->comment('异常对象类型');
            // 异常对象ID
            $table->unsignedBigInteger('target_id')->nullable()->comment('异常对象ID');
            // 平台名称
            $table->string('platform', 50)->nullable()->comment('平台名称');
            // 异常级别：critical严重/warning警告/info提示
            $table->string('severity', 20)->default('warning')->comment('异常级别');
            // 异常时间
            $table->timestamp('exception_at')->useCurrent()->comment('异常时间');
            // 异常标题
            $table->string('title', 200)->comment('异常标题');
            // 异常详情描述
            $table->text('detail')->nullable()->comment('异常详情');
            // 错误代码
            $table->string('error_code', 100)->nullable()->comment('错误代码');
            // 错误信息
            $table->text('error_message')->nullable()->comment('错误信息');
            // 处理状态：pending待处理/handling处理中/handled已处理/ignored已忽略
            $table->string('status', 20)->default('pending')->comment('处理状态');
            // 处理时间
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            // 处理人ID
            $table->foreignId('handled_by')
                ->nullable()
                
                
                ->comment('处理人ID');
            // 处理人姓名（冗余字段）
            $table->string('handler_name', 100)->nullable()->comment('处理人姓名');
            // 处理方式：manual手动/auto自动/system系统/ignored忽略
            $table->string('handle_method', 20)->nullable()->comment('处理方式');
            // 处理备注
            $table->text('handle_note')->nullable()->comment('处理备注');
            // 发生次数（同一异常可能多次发生）
            $table->unsignedInteger('occurrence_count')->default(1)->comment('发生次数');
            // 最后发生时间
            $table->timestamp('last_occurred_at')->nullable()->comment('最后发生时间');
            // 附加数据（JSON格式）
            $table->json('extra_data')->nullable()->comment('附加数据');
            // 创建时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('shop_id');
            $table->index('platform_account_id');
            $table->index('exception_type');
            $table->index('severity');
            $table->index('status');
            $table->index('exception_at');
            // 复合索引：租户+状态+级别+时间
            $table->index(['tenant_id', 'status', 'severity', 'exception_at'], 'ix_101_1');
            // 复合索引：对象类型+对象ID+状态
            $table->index(['target_type', 'target_id', 'status'], 'ix_101_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_authorization_exceptions');
    }
};