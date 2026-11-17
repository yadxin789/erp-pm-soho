<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 项目任务
 *
 * 状态 7 态（保留「逾期」，与参考目录一致）：
 *   pending           待执行
 *   in_progress       进行中
 *   for_audit         待审核
 *   completed         已完成
 *   overdue           已逾期
 *   overdue_completed 逾期完成
 *   rejected          已驳回
 */
class ProjectTask extends Model
{
    protected $table = 'tasks';

    use SoftDeletes;

    public const STATUS = [
        'pending' => '待执行',
        'in_progress' => '进行中',
        'for_audit' => '待审核',
        'completed' => '已完成',
        'overdue' => '已逾期',
        'overdue_completed' => '逾期完成',
        'rejected' => '已驳回',
    ];

    public const STATUS_COLORS = [
        'pending' => 'secondary',
        'in_progress' => 'primary',
        'for_audit' => 'info',
        'completed' => 'success',
        'overdue' => 'danger',
        'overdue_completed' => 'warning',
        'rejected' => 'dark',
    ];

    public const PRIORITY = [
        1 => '高',
        2 => '中',
        3 => '低',
    ];

    /** 终态（不会再流转） */
    public const FINISHED = ['completed', 'overdue_completed'];

    /** 审核中 */
    public const FOR_AUDIT = 'for_audit';

    protected $fillable = [
        'project_id', 'task_type_id', 'department_id', 'name', 'description',
        'executor_id', 'auditor_id', 'assignee_id',
        'status', 'priority', 'start_date', 'end_date', 'due_date',
        'extension_days', 'is_overdue', 'completed_at', 'paused_at', 'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'is_overdue' => 'boolean',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'extension_days' => 'integer',
        'priority' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProjectTask $task) {
            if ($task->wasChanged('status')) {
                ProjectLog::record(
                    $task->project_id,
                    'task_status',
                    '任务「' . $task->name . '」状态变更为「' . $task->status_label . '」',
                    ['task_id' => $task->id, 'from' => $task->getOriginal('status'), 'to' => $task->status]
                );
            }
        });
    }

    // ==================== 关系 ====================

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function taskType()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Core\Models\Department::class, 'department_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function files()
    {
        return $this->hasMany(TaskFile::class, 'task_id')->latest();
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    // ==================== 展示 ====================

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? (string) $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY[$this->priority] ?? '中';
    }

    public function getTotalHoursAttribute(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, self::FINISHED, true);
    }

    // ==================== 逾期 / 延期 ====================

    public function checkOverdue(): bool
    {
        if ($this->is_overdue || $this->isFinished()) {
            return false;
        }
        if (! $this->end_date) {
            return false;
        }
        if ($this->end_date->copy()->endOfDay()->isPast()) {
            $this->is_overdue = true;
            if ($this->status !== 'for_audit') {
                $this->status = 'overdue';
            }
            $this->save();

            return true;
        }

        return false;
    }

    public function overdueDays(): int
    {
        if (! $this->end_date || $this->isFinished()) {
            return 0;
        }
        if (! $this->end_date->copy()->endOfDay()->isPast()) {
            return 0;
        }

        return (int) $this->end_date->copy()->endOfDay()->diffInDays(now()->endOfDay());
    }

    public function extendDays(int $days): void
    {
        $base = $this->end_date ?: now();
        $this->end_date = $base->copy()->addDays($days);
        $this->due_date = $this->end_date;
        $this->extension_days = (int) $this->extension_days + $days;
        $this->is_overdue = false;
        if ($this->status === 'overdue') {
            $this->status = 'pending';
        }
        $this->save();
    }

    // ==================== 文件 ====================

    public function hasPendingFile(): bool
    {
        return $this->files()->where('status', 'pending')->exists();
    }

    public function pendingFile(): ?TaskFile
    {
        return $this->files()->where('status', 'pending')->first();
    }

    public function approvedFile(): ?TaskFile
    {
        return $this->files()->where('status', 'approved')->first();
    }

    public function latestFile(): ?TaskFile
    {
        return $this->files()->first();
    }

    // ==================== 操作权限（单一可信源）====================
    //
    // 冻结规则：只有「项目已暂停」才冻结任务的文件上传/变更/审核。

    /**
     * 上传交付文件：仅任务执行人本人；项目已暂停时禁止；已有待审核文件时禁止；
     * 已完成且已有已通过文件时改为「更新文件」流程。
     */
    public function canUploadFile(?int $userId = null): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId || ! $this->project) {
            return false;
        }
        if ((int) $this->executor_id !== $userId) {
            return false;
        }
        if ($this->project->isInactive()) {
            return false;
        }
        if ($this->hasPendingFile()) {
            return false;
        }
        if ($this->isFinished()) {
            return ! $this->files()->where('status', 'approved')->exists();
        }

        return true;
    }

    /**
     * 申请延期：仅执行人本人；项目暂停/已完成/审核中禁止。
     */
    public function canApplyExtension(?int $userId = null): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId || ! $this->project) {
            return false;
        }
        if ((int) $this->executor_id !== $userId) {
            return false;
        }
        if ($this->project->isInactive()) {
            return false;
        }
        if ($this->isFinished() || $this->status === 'for_audit' || $this->status === 'rejected') {
            return false;
        }

        return true;
    }

    /**
     * 审核：仅审核人本人，且不能自己审自己；项目暂停时禁止；
     * 状态须为待审核/已逾期，且存在待审核文件。
     */
    public function canAudit(?int $userId = null): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId || ! $this->project) {
            return false;
        }
        if ((int) $this->auditor_id !== $userId) {
            return false;
        }
        if ((int) $this->executor_id === $userId) {
            return false;
        }
        if ($this->project->isInactive()) {
            return false;
        }
        if (! in_array($this->status, ['for_audit', 'overdue'], true)) {
            return false;
        }

        return $this->hasPendingFile();
    }

    /**
     * 更新已完成的文件（生成新版本并重新送审）：执行人 / 项目创建人 / 管理员；
     * 项目暂停时禁止；须为已完成、逾期完成或已逾期，且存在已通过文件。
     */
    public function canUpdateFile(?int $userId = null): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId || ! $this->project) {
            return false;
        }

        $user = User::find($userId);
        $allowed = (int) $this->executor_id === $userId
            || (int) $this->project->creator_id === $userId
            || ($user && $user->hasRole('admin'));

        if (! $allowed) {
            return false;
        }
        if ($this->project->isInactive()) {
            return false;
        }
        if (! in_array($this->status, ['completed', 'overdue_completed', 'overdue'], true)) {
            return false;
        }

        return $this->files()->where('status', 'approved')->exists();
    }

    /**
     * 任务执行人能否开始/完成自己的任务（页面上的「开始」「标记完成」）
     */
    public function canOperateAsExecutor(?int $userId = null): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId || ! $this->project) {
            return false;
        }

        return (int) $this->executor_id === $userId && ! $this->project->isInactive() && ! $this->isFinished();
    }
}
