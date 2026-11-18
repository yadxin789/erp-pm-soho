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
        Schema::create('soho_order_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->string('from_status', 40)->nullable()->comment('原状态');
            $table->string('to_status', 40)->comment('目标状态');
            $table->string('operator_type', 20)->default('system')->comment('操作类型：system(系统), user(用户), platform(平台)');
            $table->bigInteger('operator_id')->unsigned()->nullable()->comment('操作人ID');
            $table->string('source', 30)->nullable()->comment('来源：sync(同步), manual(手动), webhook(回调)');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['order_id', 'created_at'], 'ix_28_1');
            $table->index(['tenant_id', 'order_id'], 'ix_28_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_order_status_logs');
    }
};