<?php

namespace App\Erp\Models\System;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ERP 通知模型：B-02 起统一使用 Core 共享 notifications 表（列名 content）。
 * ERP 原代码与视图使用 message 字段，这里用访问器兼容，业务层无需改动。
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'sender_id', 'module', 'type', 'title', 'content',
        'data', 'link', 'priority', 'is_read', 'read_at',
        'related_type', 'related_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function getMessageAttribute(): ?string
    {
        return $this->attributes['content'] ?? null;
    }

    public function setMessageAttribute(?string $value): void
    {
        $this->attributes['content'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now(), 'is_read' => true]);
        }
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'success' => 'green',
            'warning' => 'yellow',
            'danger'  => 'red',
            default   => 'blue',
        };
    }
}
