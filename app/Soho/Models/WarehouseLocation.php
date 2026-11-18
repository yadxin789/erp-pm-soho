<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 库区库位模型
 * 定义仓库内的库区和库位信息，支持存储、拣货、包装、退货、残次品等不同功能区域
 */
class WarehouseLocation extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'warehouse_id', 'zone_name', 'location_code',
        'location_type', 'capacity', 'current_usage', 'status', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:4',
            'current_usage' => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}