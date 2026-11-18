<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 仓库模型
 * 支持多种仓库类型：国内仓、海外仓、FBA仓、第三方仓、虚拟仓
 */
class Warehouse extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'code', 'name', 'type', 'country_code', 'address',
        'contact_name', 'contact_phone', 'priority', 'status',
    ];

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取仓库下的所有库存
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}