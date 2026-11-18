<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 通知消息模型
 * 管理系统通知、站内消息等，支持已读/未读状态
 */
class Notification extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id',
        'user_type',
        'user_id',
        'title',
        'content',
        'type',
        'priority',
        'sender',
        'data_json',
        'read_at',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'data_json' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * 访问器：是否已读
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * 访问器：优先级标签
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'high' => '高',
            'normal' => '普通',
            'low' => '低',
            default => '普通',
        };
    }

    /**
     * 标记为已读
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * 查询未读通知
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}