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
        Schema::create('soho_approval_instances', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 关联审批流
            $table->foreignId('flow_id')
                
                
                ->comment('审批流ID');
            // 审批类型：refund退款/price_change改价/batch_price批量调价/stock_adjust库存调整/product_publish商品发布/order_cancel订单取消/bill_diff账单差异确认/data_export数据导出/data_delete数据删除
            $table->string('type', 50)->comment('审批类型');
            // 业务单据类型：order订单/after_sale售后/price_rule价格规则/stock_adjustment库存调整单/product商品/bill账单/data_export导出/data_delete数据等
            $table->string('biz_type', 50)->comment('业务单据类型');
            // 业务单据ID
            $table->unsignedBigInteger('biz_id')->comment('业务单据ID');
            // 业务单号
            $table->string('biz_no', 50)->nullable()->comment('业务单号');
            // 审批标题
            $table->string('title', 200)->comment('审批标题');
            // 审批内容描述
            $table->text('content')->nullable()->comment('审批内容描述');
            // 业务数据JSON，包含审批所需的关键数据快照
            $table->json('biz_data')->nullable()->comment('业务数据(JSON)');
            // 金额或数量等关键数值
            $table->decimal('amount', 15, 4)->nullable()->comment('金额');
            // 币种
            $table->string('currency', 10)->default('CNY')->comment('币种');
            // 当前审批节点索引
            $table->unsignedInteger('current_node')->default(0)->comment('当前审批节点索引');
            // 审批节点配置快照JSON
            $table->json('nodes_snapshot')->comment('审批节点配置快照(JSON)');
            // 审批记录JSON
            $table->json('approval_logs')->nullable()->comment('审批记录(JSON)');
            // 状态：pending待审批/approving审批中/approved已通过/rejected已驳回/cancelled已撤销/expired已超时
            $table->string('status', 30)->default('pending')->comment('状态');
            // 提交人
            $table->foreignId('submitted_by')
                
                
                ->comment('提交人ID');
            // 提交时间
            $table->timestamp('submitted_at')->comment('提交时间');
            // 开始审批时间
            $table->timestamp('started_at')->nullable()->comment('开始审批时间');
            // 完成审批时间
            $table->timestamp('completed_at')->nullable()->comment('完成审批时间');
            // 超时时间
            $table->timestamp('deadline_at')->nullable()->comment('超时时间');
            // 最终审批人
            $table->foreignId('approved_by')
                ->nullable()
                
                
                ->comment('最终审批人ID');
            // 最终审批意见
            $table->string('final_remark', 500)->nullable()->comment('最终审批意见');
            // 关联店铺ID
            $table->unsignedBigInteger('shop_id')->nullable()->comment('关联店铺ID');
            // 关联仓库ID
            $table->unsignedBigInteger('warehouse_id')->nullable()->comment('关联仓库ID');
            $table->timestamps();

            // 租户+业务类型+业务ID索引
            $table->index(['tenant_id', 'biz_type', 'biz_id'], 'ix_58_1');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_58_2');
            // 租户+类型索引
            $table->index(['tenant_id', 'type'], 'ix_58_3');
            // 提交人索引
            $table->index(['tenant_id', 'submitted_by'], 'ix_58_4');
            // 店铺索引
            $table->index(['tenant_id', 'shop_id'], 'ix_58_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_approval_instances');
    }
};