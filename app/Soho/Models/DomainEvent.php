<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 领域事件模型
 * 记录领域驱动设计中的聚合根事件，用于事件驱动架构的事件发布和重放
 */
class DomainEvent extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event_type', 'aggregate_type', 'aggregate_id',
        'data', 'occurred_at', 'published_at', 'publish_status', 'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'occurred_at' => 'datetime',
            'published_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }
}