<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('code', 50)->comment('优惠券码');
            $table->string('type', 30)->comment('类型：fixed_discount(固定金额), percentage(百分比折扣)');
            $table->decimal('value', 18, 4)->comment('优惠值（金额或百分比）');
            $table->decimal('min_amount', 18, 4)->default(0)->comment('最低消费金额');
            $table->integer('total_quantity')->default(0)->comment('总发行量');
            $table->integer('used_quantity')->default(0)->comment('已使用数量');
            $table->timestamp('start_at')->nullable()->comment('生效时间');
            $table->timestamp('end_at')->nullable()->comment('失效时间');
            $table->string('status', 30)->default('active')->comment('状态：active(启用), inactive(停用), expired(已过期)');
            $table->json('applicable_products')->nullable()->comment('适用商品ID列表');
            $table->json('applicable_categories')->nullable()->comment('适用类目ID列表');
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'ux_45_1');
            $table->index(['tenant_id', 'status'], 'ix_45_2');
            $table->index(['tenant_id', 'start_at', 'end_at'], 'ix_45_3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_coupons');
    }
};