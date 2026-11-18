<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('user_type', 50)->comment('用户类型: admin管理员/shop_manager店铺经理/staff员工');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('title', 200)->comment('通知标题');
            $table->text('content')->comment('通知内容');
            $table->string('type', 30)->comment('通知类型: order订单/stock库存/payment支付/system系统');
            $table->json('data_json')->nullable()->comment('附加数据');
            $table->timestamp('read_at')->nullable()->comment('已读时间');
            $table->timestamps();

            $table->index(['tenant_id', 'user_type', 'user_id'], 'ix_50_1');
            $table->index(['tenant_id', 'type'], 'ix_50_2');
            $table->index(['tenant_id', 'read_at'], 'ix_50_3');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_notifications');
    }
};
