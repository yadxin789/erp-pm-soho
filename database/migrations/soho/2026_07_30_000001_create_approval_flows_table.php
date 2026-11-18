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
        Schema::create('soho_approval_flows', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 审批流名称
            $table->string('name', 100)->comment('审批流名称');
            // 审批流编码，用于程序引用
            $table->string('code', 50)->comment('审批流编码');
            // 审批类型：refund退款/price_change改价/batch_price批量调价/stock_adjust库存调整/product_publish商品发布/order_cancel订单取消/bill_diff账单差异确认/data_export数据导出/data_delete数据删除
            $table->string('type', 50)->comment('审批类型');
            // 作用范围：all全部/shop店铺/department部门/category商品类目
            $table->string('scope', 30)->default('all')->comment('作用范围');
            // 范围条件JSON，如指定店铺ID列表、部门ID列表、类目ID列表等
            $table->json('scope_condition')->nullable()->comment('范围条件(JSON)');
            // 触发条件JSON，如金额阈值、数量阈值等
            $table->json('trigger_condition')->nullable()->comment('触发条件(JSON)');
            // 审批流程配置JSON，包含多级审批节点
            $table->json('flow_config')->comment('审批流程配置(JSON)');
            // 超时升级配置JSON
            $table->json('escalation_config')->nullable()->comment('超时升级配置(JSON)');
            // 状态：enabled启用/disabled禁用
            $table->string('status', 20)->default('enabled')->comment('状态');
            // 优先级，数值越小优先级越高
            $table->unsignedInteger('priority')->default(0)->comment('优先级');
            // 备注说明
            $table->string('description', 255)->nullable()->comment('备注说明');
            // 创建人
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人ID');
            $table->timestamps();

            // 租户+编码唯一索引
            $table->unique(['tenant_id', 'code'], 'ux_57_1');
            // 租户+类型索引
            $table->index(['tenant_id', 'type'], 'ix_57_2');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_57_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_approval_flows');
    }
};