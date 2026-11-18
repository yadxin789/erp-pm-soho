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
        Schema::create('soho_system_parameters', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 参数键（唯一标识，如 shopee_api_key、lazada_callback_url）
            $table->string('param_key', 100)->comment('参数键');
            // 参数显示名称
            $table->string('param_name', 100)->comment('参数名称');
            // 参数值（敏感信息加密存储）
            $table->text('param_value')->nullable()->comment('参数值');
            // 分类：platform_api平台API、callback_url回调地址、secret_key密钥、other其他
            $table->string('category', 30)->default('other')->comment('分类');
            // 参数类型：string字符串、boolean布尔、integer整数、json JSON对象
            $table->string('value_type', 20)->default('string')->comment('参数类型');
            // 描述说明
            $table->string('description', 255)->nullable()->comment('描述说明');
            // 是否加密存储（敏感信息如API密钥）
            $table->boolean('is_encrypted')->default(false)->comment('是否加密');
            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');
            // 最后修改人ID
            $table->foreignId('last_modified_by')
                ->nullable()
                
                
                ->comment('最后修改人ID');
            // 默认包含 created_at 和 updated_at
            $table->timestamps();

            // 租户+参数键 唯一索引
            $table->unique(['tenant_id', 'param_key'], 'ux_86_1');
            // 分类索引
            $table->index(['tenant_id', 'category'], 'ix_86_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_system_parameters');
    }
};
