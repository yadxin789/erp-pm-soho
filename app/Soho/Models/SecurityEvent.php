<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 安全事件模型
 */
class SecurityEvent extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    protected $fillable = [
        'event_type', 'severity', 'title', 'description', 'user_id',
        'ip_address', 'status', 'handled_by', 'handled_at', 'handle_note', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function getEventTypeLabels(): array
    {
        return [
            'attack' => '攻击行为',
            'bruteforce' => '暴力破解',
            'anomaly' => '异常行为',
            'leak' => '数据泄露',
            'other' => '其他',
        ];
    }

    public static function getSeverityColors(): array
    {
        return [
            'low' => 'secondary',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
        ];
    }

    public static function getStatusLabels(): array
    {
        return [
            'open' => '待处理',
            'investigating' => '调查中',
            'resolved' => '已解决',
            'false_positive' => '误报',
        ];
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}