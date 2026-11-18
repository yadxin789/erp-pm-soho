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
        Schema::create('soho_inventory_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->string('document_type', 30)->comment('单据类型: init期初/adjust调整/inbound入库/outbound出库/transfer调拨/stocktake盘点/scrap报废');
            $table->string('document_no', 100)->comment('单据编号');
            $table->string('status', 30)->default('draft')->comment('状态: draft草稿/approved已审核/completed已完成/cancelled已取消');
            $table->integer('total_quantity')->default(0)->comment('总数量');
            $table->decimal('total_amount', 18, 4)->default(0)->comment('总金额');
            $table->bigInteger('operator_id')->unsigned()->nullable()->comment('操作员ID');
            $table->bigInteger('approved_by')->unsigned()->nullable()->comment('审核人ID');
            $table->timestamp('approved_at')->nullable()->comment('审核时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->unique(['tenant_id', 'document_no'], 'ux_66_1');
            $table->index(['tenant_id', 'warehouse_id'], 'ix_66_2');
            $table->index(['tenant_id', 'document_type'], 'ix_66_3');
            $table->index(['tenant_id', 'status'], 'ix_66_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_documents');
    }
};