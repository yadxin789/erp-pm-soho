<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 平台商品模型
 * 记录各平台的商品Listing信息
 */
class PlatformProduct extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'shop_id', 'product_id', 'platform_product_id',
        'platform_category_id', 'title', 'platform_status', 'raw_data', 'last_synced_at',
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
     * 获取所属店铺
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * 获取关联的内部商品（SPU）
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取平台SKU列表
     */
    public function platformSkus(): HasMany
    {
        return $this->hasMany(PlatformSku::class, 'platform_product_id');
    }
}