<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 库存模型
 * 记录SKU在各仓库的库存信息
 */
class Inventory extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'warehouse_id', 'sku_id', 'actual_quantity',
        'locked_quantity', 'inbound_quantity', 'in_transit_quantity',
        'damaged_quantity', 'return_pending_quantity', 'safety_quantity',
        'version', 'last_count_at',
    ];

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取所属仓库
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * 获取所属SKU
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    /**
     * 获取库存流水记录
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * 计算可用库存
     * 可用库存 = 实际库存 - 锁定库存 - 安全库存
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->actual_quantity - $this->locked_quantity - $this->safety_quantity);
    }

    /**
     * 计算可售库存（考虑在途库存）
     */
    public function getSellableQuantityAttribute(): int
    {
        return max(0, $this->actual_quantity + $this->in_transit_quantity - $this->locked_quantity - $this->safety_quantity);
    }
}