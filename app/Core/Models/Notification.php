<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 站内通知（TC-09，三源并集）。
 *
 * 字段来源：erp notifications（message/data/read_at）+ pm notifications（content/is_read/sender_id）
 *          + soho notifications（content/data_json/priority/tenant_id）。
 * module 列区分来源模块，前端按模块分组/过滤。
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'sender_id', 'module', 'type', 'title', 'content', 'data', 'link',
        'priority', 'is_read', 'read_at', 'related_type', 'related_id', 'tenant_id',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'bool',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForModule($query, ?string $module)
    {
        return $module === null ? $query : $query->where('module', $module);
    }

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }
}
