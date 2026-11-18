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
        Schema::create('soho_customer_platform_accounts', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 客户ID
            $table->foreignId('customer_id')
                ->nullable()
                
                
                ->comment('客户ID');
            // 平台编码：taobao淘宝/jd京东/amazon亚马逊/pdd拼多多/douyin抖音等
            $table->string('platform', 50)->comment('平台编码');
            // 平台客户ID（平台侧的买家标识）
            $table->string('platform_customer_id', 100)->comment('平台客户ID');
            // 店铺ID
            $table->foreignId('shop_id')
                ->nullable()
                
                
                ->comment('店铺ID');
            // 平台昵称
            $table->string('nickname', 200)->nullable()->comment('平台昵称');
            // 邮箱
            $table->string('email', 150)->nullable()->comment('邮箱');
            // 手机号
            $table->string('phone', 30)->nullable()->comment('手机号');
            // 邮箱哈希值（用于去重匹配）
            $table->string('email_hash', 64)->nullable()->comment('邮箱哈希值');
            // 手机号哈希值（用于去重匹配）
            $table->string('phone_hash', 64)->nullable()->comment('手机号哈希值');
            // 首单时间
            $table->timestamp('first_order_at')->nullable()->comment('首次下单时间');
            // 最近下单时间
            $table->timestamp('last_order_at')->nullable()->comment('最近下单时间');
            // 累计订单数
            $table->unsignedInteger('total_orders')->default(0)->comment('累计订单数');
            // 累计消费金额
            $table->decimal('total_amount', 12, 2)->default(0)->comment('累计消费金额');
            // 状态：active正常/inactive禁用/blocked锁定
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            // 租户+平台+平台客户ID唯一索引
            $table->unique(['tenant_id', 'platform', 'platform_customer_id'], 'ux_78_1');
            // 租户+客户索引
            $table->index(['tenant_id', 'customer_id'], 'ix_78_2');
            // 租户+店铺索引
            $table->index(['tenant_id', 'shop_id'], 'ix_78_3');
            // 邮箱哈希索引
            $table->index('email_hash');
            // 手机号哈希索引
            $table->index('phone_hash');
            // 最近下单时间索引
            $table->index(['tenant_id', 'last_order_at'], 'ix_78_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_customer_platform_accounts');
    }
};
