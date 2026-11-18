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
        Schema::create('soho_order_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->string('package_number', 100)->comment('包裹编号');
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->comment('发货仓库ID');
            $table->bigInteger('carrier_id')->unsigned()->nullable()->comment('物流公司ID');
            $table->string('tracking_number', 100)->nullable()->comment('运单号');
            $table->string('status', 40)->default('created')->comment('状态：created(已创建), picked(已拣货), packed(已打包), shipped(已发货), delivered(已签收), exception(异常)');
            $table->timestamp('shipped_at')->nullable()->comment('发货时间');
            $table->timestamp('delivered_at')->nullable()->comment('签收时间');
            $table->json('raw_data')->nullable()->comment('原始数据');
            $table->timestamps();

            $table->unique(['tenant_id', 'package_number'], 'ux_27_1');
            $table->index(['order_id'], 'ix_27_2');
            $table->index(['tracking_number'], 'ix_27_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_order_packages');
    }
};