<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_notification_templates', function (Blueprint $table) {
            $table->id();
            // 租户ID
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 模板编码（唯一标识）
            $table->string('template_code', 100)->comment('模板编码');
            // 模板名称
            $table->string('template_name', 100)->comment('模板名称');
            // 通知渠道：email邮件、sms短信、wechat微信、system系统消息
            $table->string('channel', 20)->default('system')->comment('通知渠道');
            // 业务类型：order订单、after_sale售后、inventory库存、finance财务、system系统、other其他
            $table->string('business_type', 30)->default('system')->comment('业务类型');
            // 模板标题（支持变量占位符）
            $table->string('subject', 200)->nullable()->comment('模板标题');
            // 模板内容（支持变量占位符如 {{order_no}}）
            $table->text('content')->comment('模板内容');
            // 可用变量列表（JSON）
            $table->json('available_variables')->nullable()->comment('可用变量(JSON)');
            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');
            // 最后修改人ID
            $table->foreignId('last_modified_by')
                ->nullable()
                
                
                ->comment('最后修改人ID');
            $table->timestamps();

            // 租户+模板编码 唯一索引
            $table->unique(['tenant_id', 'template_code'], 'ux_89_1');
            $table->index(['tenant_id', 'channel'], 'ix_89_2');
            $table->index(['tenant_id', 'business_type'], 'ix_89_3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_notification_templates');
    }
};
