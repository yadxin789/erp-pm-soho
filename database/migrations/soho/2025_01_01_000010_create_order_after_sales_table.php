<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_order_after_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_order_id')->comment('关联订单ID');
            $table->string('platform', 30)->comment('平台');
            $table->foreignId('shop_id')->comment('店铺ID');
            $table->string('after_sale_no', 200)->comment('售后单号');
            $table->string('type', 30)->comment('售后类型: refund退款/return退货/exchange换货');
            $table->string('status', 30)->default('pending')->comment('状态: pending待处理/processing处理中/completed已完成/rejected已拒绝');
            $table->decimal('refund_amount', 12, 2)->default(0)->comment('退款金额');
            $table->string('reason', 500)->nullable()->comment('售后原因');
            $table->string('buyer_nick', 200)->nullable()->comment('买家昵称');
            $table->text('description')->nullable()->comment('问题描述');
            $table->timestamp('apply_time')->nullable()->comment('申请时间');
            $table->timestamp('handle_time')->nullable()->comment('处理时间');
            $table->text('handle_result')->nullable()->comment('处理结果');
            $table->json('platform_raw_data')->nullable()->comment('平台原始数据(JSON)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_order_after_sales');
    }
};
