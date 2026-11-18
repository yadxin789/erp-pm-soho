<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 订单明细模型
 * 记录订单中的商品明细
 */
class OrderItem extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'order_id', 'sku_id', 'platform_sku_id',
        'product_name', 'sku_name', 'quantity', 'unit_price',
        'discount_amount', 'refund_amount', 'cost_price', 'profit_amount',
        'warehouse_id', 'fulfillment_status', 'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取所属订单
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }

    /**
     * 获取所属SKU
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    /**
     * 获取所属仓库
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}