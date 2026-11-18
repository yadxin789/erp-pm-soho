<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 售后明细模型
 * 记录售后单中每个商品明细的申请数量、退款数量、退货数量及退款状态
 */
class AfterSaleItem extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'after_sale_id', 'order_item_id', 'sku_id',
        'quantity', 'refund_quantity', 'return_quantity',
        'refund_amount', 'refund_status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'refund_quantity' => 'integer',
            'return_quantity' => 'integer',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function afterSale(): BelongsTo
    {
        return $this->belongsTo(OrderAfterSale::class, 'after_sale_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }
}