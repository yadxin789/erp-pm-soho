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
        Schema::create('soho_platform_accounts', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 平台编码：taobao淘宝/jd京东/amazon亚马逊/pdd拼多多/douyin抖音/shopify等
            $table->string('platform_code', 50)->comment('平台编码');
            // 平台名称
            $table->string('platform_name', 100)->comment('平台名称');
            // 账号类型：developer开发者账号/seller卖家主体/agency代理账号
            $table->string('account_type', 30)->default('developer')->comment('账号类型');
            // 开发者账号名称
            $table->string('account_name', 200)->comment('账号名称');
            // 平台开发者ID/AppKey
            $table->string('developer_id', 100)->nullable()->comment('平台开发者ID/AppKey');
            // AppSecret（加密存储）
            $table->text('app_secret_encrypted')->nullable()->comment('AppSecret(加密)');
            // 卖家主体ID（如Amazon Seller ID、淘宝卖家ID等）
            $table->string('seller_id', 100)->nullable()->comment('卖家主体ID');
            // 卖家主体名称
            $table->string('seller_name', 200)->nullable()->comment('卖家主体名称');
            // 站点/市场编码（如Amazon Marketplace ID、淘宝站点等）
            $table->string('marketplace_id', 50)->nullable()->comment('站点/市场编码');
            // 站点/市场名称
            $table->string('marketplace_name', 100)->nullable()->comment('站点/市场名称');
            // 国家/地区编码
            $table->string('country_code', 10)->nullable()->comment('国家/地区编码');
            // 国家/地区名称
            $table->string('country_name', 50)->nullable()->comment('国家/地区名称');
            // OAuth授权状态：authorized已授权/unauthorized未授权/expired已过期/revoked已撤销
            $table->string('oauth_status', 30)->default('unauthorized')->comment('OAuth授权状态');
            // 访问令牌（加密存储）
            $table->text('access_token_encrypted')->nullable()->comment('访问令牌(加密)');
            // 刷新令牌（加密存储）
            $table->text('refresh_token_encrypted')->nullable()->comment('刷新令牌(加密)');
            // Token类型
            $table->string('token_type', 30)->nullable()->comment('令牌类型');
            // 令牌过期时间
            $table->timestamp('token_expires_at')->nullable()->comment('令牌过期时间');
            // 刷新令牌过期时间
            $table->timestamp('refresh_expires_at')->nullable()->comment('刷新令牌过期时间');
            // OAuth授权范围/权限
            $table->text('scope')->nullable()->comment('OAuth授权范围');
            // API版本
            $table->string('api_version', 20)->nullable()->comment('API版本');
            // 凭据版本（用于轮换）
            $table->string('credential_version', 20)->default('v1')->comment('凭据版本');
            // LWA授权相关（Amazon）
            $table->string('lwa_client_id', 255)->nullable()->comment('LWA Client ID');
            // 平台配置JSON（如FBA/FBM配置、多站点币种时区等）
            $table->json('config')->nullable()->comment('平台配置(JSON)');
            // 附加数据JSON
            $table->json('extra_data')->nullable()->comment('附加数据(JSON)');
            // 授权时间
            $table->timestamp('authorized_at')->nullable()->comment('授权时间');
            // 最近Token刷新时间
            $table->timestamp('last_token_refresh_at')->nullable()->comment('最近Token刷新时间');
            // 最近API调用时间
            $table->timestamp('last_api_call_at')->nullable()->comment('最近API调用时间');
            // 状态：active启用/inactive禁用/suspended已暂停
            $table->string('status', 20)->default('active')->comment('状态');
            // 创建人
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人ID');
            $table->timestamps();

            // 租户+平台+开发者ID唯一索引
            $table->unique(['tenant_id', 'platform_code', 'developer_id'], 'ux_60_1');
            // 租户+卖家ID索引
            $table->index(['tenant_id', 'seller_id'], 'ix_60_2');
            // 租户+平台+状态索引
            $table->index(['tenant_id', 'platform_code', 'status'], 'ix_60_3');
            // 租户+账号类型索引
            $table->index(['tenant_id', 'account_type'], 'ix_60_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_platform_accounts');
    }
};