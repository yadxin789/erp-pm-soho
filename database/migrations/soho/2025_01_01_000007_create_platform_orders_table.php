<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_platform_orders', function (Blueprint $table) {
            $table->id();
            $table->string('platform_order_no', 200)->comment('平台订单号');
            $table->string('system_order_no', 200)->nullable()->unique('ux_10_1')->comment('系统订单号');
            $table->string('platform', 30)->comment('平台: taobao/jd/pinduoduo/amazon');
            $table->foreignId('shop_id')->comment('店铺ID');
            $table->string('order_status', 30)->default('pending')->comment('订单状态: pending待付款/paid已付款/shipped已发货/completed已完成/cancelled已取消/frozen已冻结');
            $table->string('pay_status', 30)->default('unpaid')->comment('支付状态: unpaid未支付/paid已支付/refunding退款中/refunded已退款');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('订单总金额');
            $table->decimal('pay_amount', 12, 2)->default(0)->comment('实付金额');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('优惠金额');
            $table->decimal('freight_amount', 12, 2)->default(0)->comment('运费');
            $table->string('currency', 10)->default('CNY')->comment('币种');
            $table->string('buyer_nick', 200)->nullable()->comment('买家昵称');
            $table->text('buyer_message')->nullable()->comment('买家留言');
            $table->string('receiver_name', 100)->nullable()->comment('收货人');
            $table->string('receiver_phone', 30)->nullable()->comment('收货电话');
            $table->string('receiver_province', 50)->nullable()->comment('收货省');
            $table->string('receiver_city', 50)->nullable()->comment('收货市');
            $table->string('receiver_district', 50)->nullable()->comment('收货区');
            $table->text('receiver_address')->nullable()->comment('收货详细地址');
            $table->string('receiver_zip', 20)->nullable()->comment('邮编');
            $table->timestamp('order_created_at')->nullable()->comment('平台下单时间');
            $table->timestamp('pay_time')->nullable()->comment('支付时间');
            $table->timestamp('ship_time')->nullable()->comment('发货时间');
            $table->timestamp('completed_time')->nullable()->comment('完成时间');
            $table->string('operator_remark', 500)->nullable()->comment('运营备注');
            $table->tinyInteger('is_abnormal')->default(0)->comment('异常订单: 1是 0否');
            $table->string('abnormal_reason', 500)->nullable()->comment('异常原因');
            $table->json('order_detail')->nullable()->comment('订单商品明细(JSON)');
            $table->json('platform_raw_data')->nullable()->comment('平台原始数据(JSON)');
            $table->timestamps();

            $table->index(['platform', 'shop_id'], 'ix_10_2');
            $table->index('order_status');
            $table->index('order_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_platform_orders');
    }
};
