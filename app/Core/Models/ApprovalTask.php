<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 审批待办：一步可能有多人（角色命中多人时为每人各建一条，任一人处理即推进，其余置 skipped）。
 */
class ApprovalTask extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'approval_instance_id', 'module', 'document_type', 'document_id',
        'step_order', 'step_name', 'approver_id', 'status', 'comment', 'approved_at',
    ];

    protected $casts = ['approved_at' => 'datetime', 'step_order' => 'integer'];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class, 'approval_instance_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => '待处理',
            self::STATUS_APPROVED => '已同意',
            self::STATUS_REJECTED => '已驳回',
            self::STATUS_SKIPPED => '已跳过',
            default => '已取消',
        };
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }
}
