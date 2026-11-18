<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 库存批次模型
 * 记录商品的批次信息，支持批次成本核算、生产日期、保质期管理
 */
class InventoryBatch extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'warehouse_id', 'sku_id', 'batch_no', 'quantity',
        'available_quantity', 'unit_cost', 'total_cost',
        'production_date', 'expiry_date', 'supplier_id', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'production_date' => 'date',
            'expiry_date' => 'date',
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

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }
}