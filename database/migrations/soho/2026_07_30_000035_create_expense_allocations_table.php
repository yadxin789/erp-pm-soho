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
        Schema::create('soho_expense_allocations', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 订单ID
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            // 费用类型：advertising广告费/logistics运费/warehouse仓租/platform平台费/other其他
            $table->string('expense_type', 30)->comment('费用类型');
            // 费用金额
            $table->decimal('amount', 12, 2)->default(0)->comment('费用金额');
            // 分摊方式：equal平均/percentage按百分比/weight按重量/volume按体积
            $table->string('allocation_method', 30)->comment('分摊方式');
            // 分摊配置JSON，如百分比列表、重量/体积映射等
            $table->json('allocation_config')->nullable()->comment('分摊配置(JSON)');
            // 已分摊金额
            $table->decimal('allocated_amount', 12, 2)->nullable()->comment('已分摊金额');
            $table->timestamps();

            // 租户+订单索引
            $table->index(['tenant_id', 'order_id'], 'ix_75_1');
            // 租户+费用类型索引
            $table->index(['tenant_id', 'expense_type'], 'ix_75_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_expense_allocations');
    }
};