<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 事件日志模型
 * 记录系统中所有事件，供异步处理和审计追踪
 */
class EventLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'event_type', 'model_type', 'model_id', 'user_id', 'payload_json', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * 获取触发事件的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 作用域：筛选未处理的事件
     */
    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->whereNull('processed_at');
    }
}
