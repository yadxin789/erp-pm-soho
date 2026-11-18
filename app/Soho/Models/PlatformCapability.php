<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 平台能力模型
 * 声明各平台账号所支持的功能能力、API限制、订单状态映射等，用于适配器能力查询
 */
class PlatformCapability extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform_account_id', 'platform_code', 'capability_version',
        'supports_product_pull', 'supports_product_push', 'supports_inventory_push',
        'supports_order_pull', 'supports_shipment_push', 'supports_after_sale_pull',
        'supports_refund_push', 'supports_settlement_pull', 'supports_webhook',
        'supports_order_management', 'supports_logistics_tracking',
        'supports_product_publish', 'supports_price_management',
        'supports_promotion_management', 'supports_fba', 'supports_fbm',
        'supports_multi_marketplace', 'supports_multi_currency',
        'supports_international_shipping', 'supports_tax_withholding',
        'order_pull_max_days', 'order_pull_min_days',
        'rate_limit_per_minute', 'rate_limit_per_second', 'max_page_size',
        'order_status_mapping', 'after_sale_mapping', 'extra_capabilities',
        'status', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'supports_product_pull' => 'boolean',
            'supports_product_push' => 'boolean',
            'supports_inventory_push' => 'boolean',
            'supports_order_pull' => 'boolean',
            'supports_shipment_push' => 'boolean',
            'supports_after_sale_pull' => 'boolean',
            'supports_refund_push' => 'boolean',
            'supports_settlement_pull' => 'boolean',
            'supports_webhook' => 'boolean',
            'supports_order_management' => 'boolean',
            'supports_logistics_tracking' => 'boolean',
            'supports_product_publish' => 'boolean',
            'supports_price_management' => 'boolean',
            'supports_promotion_management' => 'boolean',
            'supports_fba' => 'boolean',
            'supports_fbm' => 'boolean',
            'supports_multi_marketplace' => 'boolean',
            'supports_multi_currency' => 'boolean',
            'supports_international_shipping' => 'boolean',
            'supports_tax_withholding' => 'boolean',
            'order_pull_max_days' => 'integer',
            'order_pull_min_days' => 'integer',
            'rate_limit_per_minute' => 'integer',
            'rate_limit_per_second' => 'integer',
            'max_page_size' => 'integer',
            'order_status_mapping' => 'array',
            'after_sale_mapping' => 'array',
            'extra_capabilities' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function platformAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformAccount::class, 'platform_account_id');
    }

    /**
     * 访问器：平台中文标签
     */
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform_code) {
            'taobao'    => '淘宝',
            'jd'        => '京东',
            'pinduoduo', 'pdd' => '拼多多',
            'amazon'    => '亚马逊',
            'douyin'    => '抖音',
            'shopify'   => 'Shopify',
            default     => $this->platform_code,
        };
    }

    /**
     * 访问器：状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'    => '启用',
            'inactive'  => '禁用',
            default     => $this->status,
        };
    }
}