<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 商品模型（SPU）
 * 标准产品单元，包含商品的基本信息
 */
class Product extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_code', 'name', 'subtitle', 'brand_id', 'category_id',
        'status', 'description', 'cost_price', 'retail_price', 'weight', 'attributes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
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
     * 获取所属品牌
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * 获取所属类目
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * 获取商品的SKU列表
     */
    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    /**
     * 获取平台商品关联
     */
    public function platformProducts(): HasMany
    {
        return $this->hasMany(PlatformProduct::class);
    }

    /**
     * 获取商品图片
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}