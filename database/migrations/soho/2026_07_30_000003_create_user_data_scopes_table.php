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
        Schema::create('soho_user_data_scopes', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 用户ID
            $table->foreignId('user_id')
                
                
                ->comment('用户ID');
            // 角色ID（可选，角色级别的数据权限）
            $table->foreignId('role_id')
                ->nullable()
                
                
                ->comment('角色ID');
            // 权限主体类型：user用户/role角色
            $table->string('subject_type', 20)->default('user')->comment('权限主体类型');
            // 数据域：shop店铺/warehouse仓库/platform平台/department部门/product_category商品类目/customer_field客户字段/finance_field财务字段
            $table->string('domain', 50)->comment('数据域');
            // 权限类型：view查看/edit操作/export导出/delete删除
            $table->string('action', 30)->comment('权限类型');
            // 范围策略：all全部/specified指定/exclude排除
            $table->string('scope_strategy', 30)->default('specified')->comment('范围策略');
            // 范围值JSON，如店铺ID列表、仓库ID列表等
            $table->json('scope_values')->nullable()->comment('范围值(JSON)');
            // 字段级权限配置JSON，如客户可查看的字段、财务可查看的字段
            $table->json('field_permissions')->nullable()->comment('字段级权限配置(JSON)');
            // 敏感数据级别：normal普通/sensitive敏感/high_sensitive高敏感
            $table->string('sensitivity_level', 20)->default('normal')->comment('敏感数据级别');
            // 是否允许导出
            $table->boolean('can_export')->default(false)->comment('是否允许导出');
            // 导出数据最大条数限制
            $table->unsignedInteger('export_limit')->nullable()->comment('导出数据最大条数限制');
            // 审批金额阈值，超过此金额需要审批（仅审批相关操作）
            $table->decimal('approval_threshold', 15, 4)->nullable()->comment('审批金额阈值');
            // 改价幅度限制（百分比）
            $table->decimal('price_change_limit', 8, 4)->nullable()->comment('改价幅度限制(%)');
            // 库存调整上限
            $table->decimal('stock_adjust_limit', 15, 4)->nullable()->comment('库存调整上限');
            // 生效开始时间
            $table->timestamp('effective_at')->nullable()->comment('生效开始时间');
            // 生效结束时间
            $table->timestamp('expires_at')->nullable()->comment('生效结束时间');
            // 状态：enabled启用/disabled禁用
            $table->string('status', 20)->default('enabled')->comment('状态');
            // 备注
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamps();

            // 租户+用户+数据域+权限类型唯一索引
            $table->unique(['tenant_id', 'user_id', 'domain', 'action'], 'ux_59_1');
            // 租户+角色索引
            $table->index(['tenant_id', 'role_id'], 'ix_59_2');
            // 租户+数据域索引
            $table->index(['tenant_id', 'domain'], 'ix_59_3');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_59_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_user_data_scopes');
    }
};