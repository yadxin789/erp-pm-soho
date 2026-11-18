<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 退款记录模型
 * 记录每次退款操作的详细信息，包括退款类型、金额、状态、平台流水号等
 */
class RefundRecord extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'after_sale_id', 'order_id', 'refund_no',
        'refund_type', 'refund_amount', 'refund_status',
        'platform_refund_no', 'failed_reason', 'platform_response',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'platform_response' => 'array',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }
}