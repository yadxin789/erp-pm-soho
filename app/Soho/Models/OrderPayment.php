<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 订单支付模型
 * 记录订单的支付信息，包括支付方式、状态、交易流水等
 */
class OrderPayment extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'order_id', 'payment_method', 'payment_status', 'amount',
        'transaction_no', 'paid_at', 'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'paid_at'    => 'datetime',
            'raw_data'   => 'array',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取所属订单
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }
}
