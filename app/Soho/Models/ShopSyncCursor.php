<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 店铺同步游标模型
 * 记录每个店铺在各同步类型上的游标位置，用于增量同步和断点续传
 */
class ShopSyncCursor extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'shop_id', 'platform_code', 'sync_type', 'sync_mode',
        'cursor', 'cursor_type', 'last_sync_start_at', 'last_sync_end_at',
        'last_success_at', 'last_failure_at', 'last_sync_duration',
        'overlap_window', 'next_sync_at', 'sync_interval', 'batch_size',
        'max_retries', 'retry_count', 'consecutive_failures',
        'last_error_message', 'last_error_code', 'last_error_detail',
        'last_fetched_count', 'total_synced_count', 'total_success_count',
        'total_failure_count', 'pagination_state', 'config',
        'status', 'is_enabled', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_start_at' => 'datetime',
            'last_sync_end_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'last_sync_duration' => 'integer',
            'overlap_window' => 'integer',
            'next_sync_at' => 'datetime',
            'sync_interval' => 'integer',
            'batch_size' => 'integer',
            'max_retries' => 'integer',
            'retry_count' => 'integer',
            'consecutive_failures' => 'integer',
            'last_error_detail' => 'array',
            'last_fetched_count' => 'integer',
            'total_synced_count' => 'integer',
            'total_success_count' => 'integer',
            'total_failure_count' => 'integer',
            'pagination_state' => 'array',
            'config' => 'array',
            'is_enabled' => 'boolean',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}