<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 库存单据模型
 * 记录库存相关的业务单据，包括期初、调整、入库、出库、调拨、盘点、报废等类型
 */
class InventoryDocument extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'warehouse_id', 'document_type', 'document_no', 'status',
        'total_quantity', 'total_amount', 'operator_id', 'approved_by',
        'approved_at', 'completed_at', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
            'total_amount' => 'decimal:4',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}