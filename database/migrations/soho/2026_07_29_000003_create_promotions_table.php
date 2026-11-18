<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('name', 255)->comment('促销名称');
            $table->string('type', 30)->comment('类型：fixed_discount(固定金额), percentage(百分比折扣), buy_get(买赠)');
            $table->decimal('value', 18, 4)->comment('优惠值（金额或百分比）');
            $table->decimal('min_amount', 18, 4)->default(0)->comment('最低消费金额');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
            $table->string('status', 30)->default('draft')->comment('状态：draft(草稿), active(启用), inactive(停用), expired(已过期)');
            $table->boolean('stackable')->default(false)->comment('是否可叠加');
            $table->json('conditions_json')->nullable()->comment('附加条件（如指定商品、数量等）');
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'ix_44_1');
            $table->index(['tenant_id', 'start_at', 'end_at'], 'ix_44_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_promotions');
    }
};