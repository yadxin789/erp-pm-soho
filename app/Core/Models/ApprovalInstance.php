<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 审批实例：一次具体的报批（单据 type+id 由业务侧给出，Core 不认业务实体）。
 *
 * status：pending / approved / rejected / cancelled
 * current_step 是当前待办步骤序号（1 起，没有待办时为总步骤数）。
 */
class ApprovalInstance extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'approval_flow_id', 'module', 'document_type', 'document_id', 'title',
        'applicant_id', 'current_step', 'status', 'submitted_at', 'finished_at', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'current_step' => 'integer',
        'submitted_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ApprovalTask::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => '审批中',
            self::STATUS_APPROVED => '已通过',
            self::STATUS_REJECTED => '已驳回',
            self::STATUS_CANCELLED => '已撤销',
            default => $this->status,
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warn',
            self::STATUS_APPROVED => 'ok',
            self::STATUS_REJECTED => 'danger',
            default => 'muted',
        };
    }

    public function scopeForModule($q, string $module)
    {
        return $q->where('module', $module);
    }
}
