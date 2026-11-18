<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 财务交易流水模型
 * 记录平台结算相关的每一笔交易明细，支持多币种、多方向、多类型的财务核算
 */
class FinancialTransaction extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'legal_entity_id', 'shop_id', 'platform',
        'order_id', 'settlement_id', 'transaction_type', 'direction',
        'amount', 'original_amount', 'currency', 'exchange_rate',
        'transaction_no', 'platform_trade_no',
        'transaction_time', 'booking_time',
        'raw_data', 'reconcile_status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'transaction_time' => 'datetime',
            'booking_time' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(FinanceSettle::class, 'settlement_id');
    }
}