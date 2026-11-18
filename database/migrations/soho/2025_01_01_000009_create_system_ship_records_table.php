<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_system_ship_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_order_id')->comment('订单ID');
            $table->foreignId('express_company_id')->nullable()->comment('快递公司ID');
            $table->string('tracking_number', 200)->comment('物流单号');
            $table->string('ship_status', 30)->default('pending')->comment('发货状态: pending待推送/success已推送/failed推送失败');
            $table->text('ship_error')->nullable()->comment('发货失败原因');
            $table->foreignId('operator_id')->nullable()->comment('操作人');
            $table->timestamp('ship_time')->nullable()->comment('实际发货时间');
            $table->timestamps();

            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_system_ship_records');
    }
};
