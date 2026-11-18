<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 品牌模型
 * 管理商品品牌信息
 */
class Brand extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'logo', 'description', 'status',
    ];

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取品牌下的商品
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}