<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 价格调价申请表迁移
 * 记录商品或SKU的价格调整申请流程，包括申请人、审批人、价格变动详情及审批状态
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_price_change_requests', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 关联商品ID
            $table->foreignId('product_id')
                ->nullable()
                
                
                ->comment('商品ID');
            // 关联SKU ID
            $table->foreignId('sku_id')->nullable()->comment('SKU ID');
            // SKU编码（冗余字段，便于查询）
            $table->string('sku_code', 100)->nullable()->comment('SKU编码');
            // 当前价格
            $table->decimal('current_price', 12, 2)->comment('当前价格');
            // 申请价格
            $table->decimal('requested_price', 12, 2)->comment('申请价格');
            // 变动金额 = 申请价格 - 当前价格
            $table->decimal('change_amount', 12, 2)->comment('变动金额');
            // 变动百分比
            $table->decimal('change_percentage', 5, 2)->nullable()->comment('变动百分比');
            // 调价原因
            $table->string('reason', 200)->nullable()->comment('调价原因');
            // 备注
            $table->text('remark')->nullable()->comment('备注');
            // 状态：pending待审批/approved已批准/rejected已驳回/cancelled已取消
            $table->string('status', 30)->default('pending')->comment('状态:pending待审批/approved已批准/rejected已驳回/cancelled已取消');
            // 申请人ID
            $table->foreignId('requested_by')
                ->nullable()
                
                
                ->comment('申请人');
            // 申请人姓名（冗余字段，便于查询）
            $table->string('requested_by_name', 100)->nullable()->comment('申请人姓名');
            // 审批人ID
            $table->foreignId('approved_by')
                ->nullable()
                
                
                ->comment('审批人');
            // 审批人姓名（冗余字段，便于查询）
            $table->string('approved_by_name', 100)->nullable()->comment('审批人姓名');
            // 审批备注
            $table->text('approval_remark')->nullable()->comment('审批备注');
            // 审批时间
            $table->timestamp('approved_at')->nullable()->comment('审批时间');
            // 申请时间
            $table->timestamp('requested_at')->useCurrent()->comment('申请时间');
            // 创建时间和更新时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('product_id');
            $table->index('sku_id');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_price_change_requests');
    }
};
