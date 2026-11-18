<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SKU模型
 * 具体销售规格，包含价格、库存等详细信息
 */
class ProductSku extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'product_id', 'sku_code', 'barcode', 'external_sku',
        'name', 'specification', 'cost_price', 'purchase_price', 'sale_price',
        'weight', 'length', 'width', 'height', 'safety_quantity', 'status',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
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
     * 获取所属商品（SPU）
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取库存记录
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * 获取平台SKU映射
     */
    public function platformSkus(): HasMany
    {
        return $this->hasMany(PlatformSku::class);
    }

    /**
     * 获取图片
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}