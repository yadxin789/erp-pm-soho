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
        Schema::create('soho_inspection_records', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 售后ID，关联售后主表
            $table->foreignId('after_sale_id')
                
                
                ->comment('售后ID');
            // 退货包裹ID，关联退货包裹表
            $table->foreignId('return_shipment_id')
                
                
                ->comment('退货包裹ID');
            // SKU ID，关联商品SKU
            $table->foreignId('sku_id')
                ->nullable()
                
                
                ->comment('SKU ID');
            // 实收数量
            $table->integer('received_quantity')->default(0)->comment('实收数量');
            // 合格数量
            $table->integer('qualified_quantity')->default(0)->comment('合格数量');
            // 次品数量
            $table->integer('defective_quantity')->default(0)->comment('次品数量');
            // 质检结果：qualified合格/defective次品/rework需返工/scrap报废
            $table->string('inspection_result', 30)->comment('质检结果');
            // 质检员ID
            $table->foreignId('inspector_id')
                ->nullable()
                
                
                ->comment('质检员ID');
            // 质检时间
            $table->timestamp('inspected_at')->nullable()->comment('质检时间');
            // 备注
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->timestamps();

            // 租户+售后ID索引
            $table->index(['tenant_id', 'after_sale_id'], 'ix_73_1');
            // 租户+质检结果索引
            $table->index(['tenant_id', 'inspection_result'], 'ix_73_2');
            // 退货包裹ID索引
            $table->index('return_shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_inspection_records');
    }
};