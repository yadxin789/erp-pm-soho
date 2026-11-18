<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 物流轨迹模型
 * 记录包裹的物流轨迹信息
 */
class LogisticsTrack extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'package_id', 'tracking_number', 'original_status',
        'unified_status', 'location', 'description', 'track_time', 'source', 'raw_data',
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
     * 获取所属包裹
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class, 'package_id');
    }
}