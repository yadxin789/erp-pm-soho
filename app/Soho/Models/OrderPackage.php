<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 订单包裹模型
 * 记录订单发货包裹信息
 */
class OrderPackage extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'order_id', 'package_number', 'warehouse_id',
        'carrier_id', 'tracking_number', 'status', 'shipped_at', 'delivered_at', 'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
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

    /**
     * 获取发货仓库
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * 获取物流公司
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(LogisticsCompany::class, 'carrier_id');
    }

    /**
     * 获取物流轨迹记录
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(LogisticsTrack::class);
    }
}