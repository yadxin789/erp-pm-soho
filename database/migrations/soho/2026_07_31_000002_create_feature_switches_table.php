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
        Schema::create('soho_feature_switches', function (Blueprint $table) {
            $table->id();
            // 租户ID
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 功能标识（唯一，如 order_sync、auto_fulfillment、stock_alert）
            $table->string('feature_key', 100)->comment('功能标识');
            // 功能名称
            $table->string('feature_name', 100)->comment('功能名称');
            // 功能描述
            $table->string('description', 255)->nullable()->comment('功能描述');
            // 所属模块：order订单、product商品、inventory库存、logistics物流、finance财务、system系统、other其他
            $table->string('module', 30)->default('system')->comment('所属模块');
            // 是否启用
            $table->boolean('is_enabled')->default(false)->comment('是否启用');
            // 额外配置（JSON）
            $table->json('config')->nullable()->comment('额外配置(JSON)');
            // 启用人ID
            $table->foreignId('enabled_by')
                ->nullable()
                
                
                ->comment('启用人ID');
            // 启用时间
            $table->timestamp('enabled_at')->nullable()->comment('启用时间');
            // 最后修改人ID
            $table->foreignId('last_modified_by')
                ->nullable()
                
                
                ->comment('最后修改人ID');
            // 默认包含 created_at 和 updated_at
            $table->timestamps();

            // 租户+功能键 唯一索引
            $table->unique(['tenant_id', 'feature_key'], 'ux_87_1');
            // 模块索引
            $table->index(['tenant_id', 'module'], 'ix_87_2');
            // 启用状态索引
            $table->index(['tenant_id', 'is_enabled'], 'ix_87_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_feature_switches');
    }
};
