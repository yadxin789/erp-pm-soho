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
        Schema::create('soho_platform_capabilities', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 关联平台账号
            $table->foreignId('platform_account_id')
                
                
                ->comment('平台账号ID');
            // 平台编码（冗余，便于查询）
            $table->string('platform_code', 50)->comment('平台编码');
            // 能力版本
            $table->string('capability_version', 20)->default('v1')->comment('能力版本');
            // 是否支持商品拉取
            $table->boolean('supports_product_pull')->default(false)->comment('是否支持商品拉取');
            // 是否支持商品推送（发布）
            $table->boolean('supports_product_push')->default(false)->comment('是否支持商品推送');
            // 是否支持库存推送
            $table->boolean('supports_inventory_push')->default(false)->comment('是否支持库存推送');
            // 是否支持订单拉取
            $table->boolean('supports_order_pull')->default(false)->comment('是否支持订单拉取');
            // 是否支持发货推送
            $table->boolean('supports_shipment_push')->default(false)->comment('是否支持发货推送');
            // 是否支持售后拉取
            $table->boolean('supports_after_sale_pull')->default(false)->comment('是否支持售后拉取');
            // 是否支持退款推送
            $table->boolean('supports_refund_push')->default(false)->comment('是否支持退款推送');
            // 是否支持结算拉取
            $table->boolean('supports_settlement_pull')->default(false)->comment('是否支持结算拉取');
            // 是否支持Webhook
            $table->boolean('supports_webhook')->default(false)->comment('是否支持Webhook');
            // 是否支持订单状态管理
            $table->boolean('supports_order_management')->default(false)->comment('是否支持订单状态管理');
            // 是否支持物流轨迹查询
            $table->boolean('supports_logistics_tracking')->default(false)->comment('是否支持物流轨迹查询');
            // 是否支持商品发布
            $table->boolean('supports_product_publish')->default(false)->comment('是否支持商品发布');
            // 是否支持价格管理
            $table->boolean('supports_price_management')->default(false)->comment('是否支持价格管理');
            // 是否支持促销管理
            $table->boolean('supports_promotion_management')->default(false)->comment('是否支持促销管理');
            // 是否支持FBA
            $table->boolean('supports_fba')->default(false)->comment('是否支持FBA');
            // 是否支持FBM
            $table->boolean('supports_fbm')->default(false)->comment('是否支持FBM');
            // 是否支持多站点
            $table->boolean('supports_multi_marketplace')->default(false)->comment('是否支持多站点');
            // 是否支持多币种
            $table->boolean('supports_multi_currency')->default(false)->comment('是否支持多币种');
            // 是否支持国际物流
            $table->boolean('supports_international_shipping')->default(false)->comment('是否支持国际物流');
            // 是否支持税务代扣
            $table->boolean('supports_tax_withholding')->default(false)->comment('是否支持税务代扣');
            // 订单最大拉取时间跨度（天）
            $table->unsignedInteger('order_pull_max_days')->default(30)->comment('订单最大拉取时间跨度(天)');
            // 订单最小拉取时间跨度（天）
            $table->unsignedInteger('order_pull_min_days')->default(1)->comment('订单最小拉取时间跨度(天)');
            // API限流（每分钟请求数）
            $table->unsignedInteger('rate_limit_per_minute')->default(100)->comment('API限流(次/分钟)');
            // API限流（每秒请求数）
            $table->unsignedInteger('rate_limit_per_second')->nullable()->comment('API限流(次/秒)');
            // 分页最大条数
            $table->unsignedInteger('max_page_size')->default(100)->comment('分页最大条数');
            // 订单状态映射配置JSON
            $table->json('order_status_mapping')->nullable()->comment('订单状态映射配置(JSON)');
            // 售后类型映射配置JSON
            $table->json('after_sale_mapping')->nullable()->comment('售后类型映射配置(JSON)');
            // 能力扩展配置JSON
            $table->json('extra_capabilities')->nullable()->comment('能力扩展配置(JSON)');
            // 状态：active启用/inactive禁用
            $table->string('status', 20)->default('active')->comment('状态');
            // 备注
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamps();

            // 租户+平台账号唯一索引
            $table->unique(['tenant_id', 'platform_account_id'], 'ux_61_1');
            // 租户+平台编码索引
            $table->index(['tenant_id', 'platform_code'], 'ix_61_2');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_61_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_platform_capabilities');
    }
};