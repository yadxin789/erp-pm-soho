<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 履约单模型
 * 记录订单的履约流程，包括配货、拣货、打包、发货等环节，支持分仓和FBM/FBA模式
 */
class FulfillmentOrder extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'order_id', 'shop_id', 'warehouse_id',
        'fulfillment_mode', 'status', 'priority',
        'total_items', 'total_quantity', 'total_weight',
        'picked_quantity', 'packed_quantity', 'shipped_quantity',
        'picked_at', 'packed_at', 'shipped_at', 'completed_at',
        'operator_id', 'picker_id', 'packer_id',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'total_items' => 'integer',
            'total_quantity' => 'integer',
            'total_weight' => 'decimal:2',
            'picked_quantity' => 'integer',
            'packed_quantity' => 'integer',
            'shipped_quantity' => 'integer',
            'picked_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'priority' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function picker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FulfillmentItem::class, 'fulfillment_order_id');
    }
}