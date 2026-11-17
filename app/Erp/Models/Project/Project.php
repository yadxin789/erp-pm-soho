<?php

namespace App\Erp\Models\Project;

use App\Erp\Models\MasterData\Customer;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 项目（产品研发项目模块）
 *
 * 状态 4 态（不含逾期：逾期由「进度 + 日期」推导，仅做展示与提醒，不改状态）：
 *   not_started 立项中（未启动）
 *   in_progress 进行中
 *   suspended   已暂停
 *   completed   已完成
 */
class Project extends Model
{
    protected $table = 'projects';

    use SoftDeletes;

    public const STATUS = [
        'not_started' => '立项中',
        'in_progress' => '进行中',
        'suspended' => '已暂停',
        'completed' => '已完成',
    ];

    public const STATUS_COLORS = [
        'not_started' => 'secondary',
        'in_progress' => 'primary',
        'suspended' => 'warning',
        'completed' => 'success',
    ];

    protected $fillable = [
        'name', 'code', 'version', 'product_name', 'product_model',
        'description', 'status', 'progress',
        'creator_id', 'manager_id', 'customer_id',
        'start_date', 'end_date', 'budget',
        'paused_reason', 'paused_at', 'resumed_at', 'total_paused_days',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'progress' => 'decimal:2',
        'budget' => 'decimal:4',
        'total_paused_days' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (Project $project) {
            if ($project->wasChanged('status')) {
                ProjectLog::record($project->id, 'status', '项目状态变更为「' . $project->status_label . '」', [
                    'from' => $project->getOriginal('status'),
                    'to' => $project->status,
                ]);
            }
        });
    }

    // ==================== 关系 ====================

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    public function logs()
    {
        return $this->hasMany(ProjectLog::class, 'project_id')->latest();
    }

    // ==================== 访问范围 ====================

    /**
     * 只有「自己参与的项目」可见。
     * 参与 = 项目创建人 / 项目负责人 / 任一任务的执行人或审核人
     */
    public function scopeVisibleTo($query, $user = null)
    {
        $userId = $user instanceof User ? (int) $user->id : ($user ? (int) $user : (int) auth()->id());

        if (! $userId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($userId) {
            $q->where('creator_id', $userId)
                ->orWhere('manager_id', $userId)
                ->orWhereHas('tasks', function ($task) use ($userId) {
                    $task->where('executor_id', $userId)->orWhere('auditor_id', $userId);
                });
        });
    }

    public function isParticipant($user = null): bool
    {
        $userId = $user instanceof User ? (int) $user->id : ($user ? (int) $user : (int) auth()->id());

        if (! $userId) {
            return false;
        }
        if ((int) $this->creator_id === $userId || (int) $this->manager_id === $userId) {
            return true;
        }

        return $this->tasks()
            ->where(function ($q) use ($userId) {
                $q->where('executor_id', $userId)->orWhere('auditor_id', $userId);
            })
            ->exists();
    }

    /**
     * 项目参与人（创建人 + 负责人 + 全部任务的执行人与审核人）
     */
    public function participants()
    {
        $ids = $this->tasks()->pluck('executor_id')
            ->merge($this->tasks()->pluck('auditor_id'))
            ->push($this->creator_id)
            ->push($this->manager_id)
            ->filter()
            ->unique()
            ->values();

        return User::whereIn('id', $ids)->orderBy('name')->get();
    }

    public function members()
    {
        return $this->participants();
    }

    // ==================== 状态与进度 ====================

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? (string) $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /** 项目累计工时 */
    public function getTotalHoursAttribute(): float
    {
        return (float) TimeEntry::whereIn('project_task_id', $this->tasks()->pluck('id'))->sum('hours');
    }

    public function isInactive(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * 项目是否逾期：仅供展示/提醒，不改变状态。
     * 已完成的项目不算逾期。
     */
    public function isOverdue(): bool
    {
        if (! $this->end_date || $this->status === 'completed') {
            return false;
        }

        return $this->end_date->copy()->endOfDay()->isPast();
    }

    public function overdueDays(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return (int) $this->end_date->copy()->endOfDay()->diffInDays(now()->endOfDay());
    }

    /**
     * 重算进度并驱动状态：
     * - 任一任务开工（进行中/待审核/逾期/已完成）且项目处于「立项中」→ 进行中
     * - 全部任务完成 → 已完成
     * - 项目已暂停 → 冻结，不做任何流转
     */
    public function calculateProgress(): float
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->whereIn('status', ['completed', 'overdue_completed'])->count();

        $progress = $total > 0 ? round($completed / $total * 100, 2) : 0;

        $this->progress = $progress;

        if ($this->status === 'suspended') {
            $this->saveQuietly();
            return $progress;
        }

        $started = $this->tasks()->whereIn('status', ['in_progress', 'for_audit', 'overdue', 'overdue_completed', 'completed'])->exists();

        if ($total > 0 && $completed === $total) {
            $this->status = 'completed';
        } elseif ($started && $this->status === 'not_started') {
            $this->status = 'in_progress';
        }

        $this->saveQuietly();

        return $progress;
    }

    // ==================== 操作权限（创建人 / 管理员）====================

    protected function canOperate(?int $userId): bool
    {
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        if (! $userId) {
            return false;
        }
        if ((int) $this->creator_id === $userId) {
            return true;
        }

        $user = User::find($userId);

        return $user && $user->hasRole('admin');
    }

    public function canPause(?int $userId = null): bool
    {
        return $this->canOperate($userId) && $this->status === 'in_progress';
    }

    public function canResume(?int $userId = null): bool
    {
        return $this->canOperate($userId) && $this->status === 'suspended';
    }

    public function canChange(?int $userId = null): bool
    {
        return $this->canOperate($userId) && $this->status !== 'completed';
    }

    public function canEdit(?int $userId = null): bool
    {
        return $this->canOperate($userId);
    }

    public function canDelete(?int $userId = null): bool
    {
        return $this->canOperate($userId);
    }
}
