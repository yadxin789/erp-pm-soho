<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('name', 100)->comment('分组名称');
            $table->string('code', 50)->nullable()->comment('分组编码');
            $table->string('color', 20)->nullable()->comment('标签颜色');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'code'], 'ix_41_1');
        });

        Schema::create('soho_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->foreignId('group_id')
                ->nullable()
                
                
                ->comment('客户分组ID');
            $table->string('name', 100)->comment('客户名称/昵称');
            $table->string('email', 150)->nullable()->comment('邮箱');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->string('password', 255)->nullable()->comment('登录密码');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->decimal('total_spent', 12, 2)->default(0)->comment('累计消费金额');
            $table->unsignedInteger('order_count')->default(0)->comment('订单数量');
            $table->timestamp('last_order_at')->nullable()->comment('最近下单时间');
            $table->text('notes')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status'], 'ix_41_2');
            $table->index(['tenant_id', 'group_id'], 'ix_41_3');
            $table->index('email');
            $table->index('phone');
            $table->index('last_order_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_customers');
        Schema::dropIfExists('soho_customer_groups');
    }
};
