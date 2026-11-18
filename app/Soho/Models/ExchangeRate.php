<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 汇率模型
 * 管理不同货币之间的兑换比率，支持多源汇率和历史汇率查询
 */
class ExchangeRate extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'from_currency', 'to_currency', 'rate',
        'rate_source', 'rate_date', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'rate_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}