<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 审计日志模型
 * 记录系统关键操作行为，支持数据变更追溯和安全审计
 */
class AuditLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'action', 'module', 'target_type', 'target_id',
        'old_value', 'new_value', 'ip_address', 'user_agent', 'status',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'json',
            'new_value' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}