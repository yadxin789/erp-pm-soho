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
        Schema::create('soho_shop_credentials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('shop_id')->unsigned()->comment('店铺ID');
            $table->string('client_id', 255)->nullable()->comment('客户端ID');
            $table->text('client_secret_encrypted')->nullable()->comment('客户端密钥(加密)');
            $table->text('access_token_encrypted')->nullable()->comment('访问令牌(加密)');
            $table->text('refresh_token_encrypted')->nullable()->comment('刷新令牌(加密)');
            $table->string('token_type', 30)->nullable()->comment('令牌类型');
            $table->timestamp('token_expires_at')->nullable()->comment('令牌过期时间');
            $table->timestamp('refresh_expires_at')->nullable()->comment('刷新令牌过期时间');
            $table->text('scope')->nullable()->comment('权限范围');
            $table->json('extra_data')->nullable()->comment('额外数据');
            $table->timestamp('authorized_at')->nullable()->comment('授权时间');
            $table->timestamp('last_refresh_at')->nullable()->comment('最近刷新时间');
            $table->timestamps();

            $table->unique(['shop_id'], 'ux_30_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_shop_credentials');
    }
};