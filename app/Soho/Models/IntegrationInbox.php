<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 集成收件箱模型
 * 接收来自各平台的Webhook事件和同步数据，支持幂等去重和失败重试
 */
class IntegrationInbox extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'source_platform', 'event_type', 'event_id',
        'payload', 'headers', 'processed_at', 'process_status',
        'retry_count', 'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'processed_at' => 'datetime',
            'retry_count' => 'integer',
            'next_retry_at' => 'datetime',
        ];
    }
}