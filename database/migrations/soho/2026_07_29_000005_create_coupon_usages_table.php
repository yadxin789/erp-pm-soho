<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_coupon_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('coupon_id')->unsigned()->comment('优惠券ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->bigInteger('user_id')->unsigned()->nullable()->comment('用户ID');
            $table->decimal('discount_amount', 18, 4)->default(0)->comment('实际优惠金额');
            $table->string('status', 30)->default('used')->comment('状态：used(已使用), refunded(已退款)');
            $table->timestamps();

            $table->index(['tenant_id', 'coupon_id'], 'ix_46_1');
            $table->index(['tenant_id', 'order_id'], 'ix_46_2');
            $table->index(['tenant_id', 'user_id'], 'ix_46_3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_coupon_usages');
    }
};