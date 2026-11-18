<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_shops', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->comment('平台: taobao/jd/pinduoduo/amazon');
            $table->string('shop_name', 200)->comment('店铺名称');
            $table->string('shop_code', 100)->nullable()->comment('店铺编码');
            $table->string('shop_url', 500)->nullable()->comment('店铺链接');
            $table->text('app_key')->nullable()->comment('平台AppKey/ClientID(加密存储)');
            $table->text('app_secret')->nullable()->comment('平台AppSecret/密钥(加密存储)');
            $table->text('access_token')->nullable()->comment('授权Token(加密存储)');
            $table->text('refresh_token')->nullable()->comment('刷新Token(加密存储)');
            $table->timestamp('token_expires_at')->nullable()->comment('Token过期时间');
            $table->tinyInteger('auth_status')->default(0)->comment('授权状态: 1已授权 0未授权 2已过期');
            $table->tinyInteger('status')->default(1)->comment('店铺状态: 1启用 0停用');
            $table->tinyInteger('sync_enabled')->default(1)->comment('同步开关: 1开启 0关闭');
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->json('platform_config')->nullable()->comment('平台额外配置(JSON)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_shops');
    }
};
