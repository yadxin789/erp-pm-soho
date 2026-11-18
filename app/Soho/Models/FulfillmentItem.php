<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 履约明细模型
 * 记录履约单中每个商品明细的处理进度，包括拣货、打包、发货数量
 */
class FulfillmentItem extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'fulfillment_order_id', 'order_item_id', 'sku_id',
        'warehouse_location_id', 'batch_no',
        'quantity', 'picked_quantity', 'packed_quantity', 'shipped_quantity',
        'status', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'picked_quantity' => 'integer',
            'packed_quantity' => 'integer',
            'shipped_quantity' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fulfillmentOrder(): BelongsTo
    {
        return $this->belongsTo(FulfillmentOrder::class, 'fulfillment_order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}