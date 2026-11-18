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
        Schema::create('soho_return_shipments', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 售后ID，关联售后主表
            $table->foreignId('after_sale_id')
                
                
                ->comment('售后ID');
            // 订单ID
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            // 退货包裹号
            $table->string('return_no', 50)->unique('ux_72_1')->comment('退货包裹号');
            // 物流公司编码
            $table->string('carrier_code', 50)->comment('物流公司编码');
            // 物流跟踪号
            $table->string('tracking_no', 100)->comment('物流跟踪号');
            // 收件人姓名
            $table->string('receiver_name', 100)->comment('收件人姓名');
            // 收件人电话
            $table->string('receiver_phone', 30)->comment('收件人电话');
            // 实际收件时间
            $table->timestamp('received_at')->nullable()->comment('实际收件时间');
            // 收件仓库ID
            $table->foreignId('warehouse_id')
                ->nullable()
                
                
                ->comment('收件仓库ID');
            // 状态：shipped已寄出/received已收件/inspecting质检中/completed已完成/rejected已拒收
            $table->string('status', 30)->default('shipped')->comment('退货状态');
            $table->timestamps();

            // 租户+售后ID索引
            $table->index(['tenant_id', 'after_sale_id'], 'ix_72_2');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_72_3');
            // 物流跟踪号索引
            $table->index('tracking_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_return_shipments');
    }
};