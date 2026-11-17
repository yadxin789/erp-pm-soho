<?php

namespace App\Pm\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'name', 'code', 'version', 'product_name', 'product_model', 'creator_id',
        'start_date', 'end_date', 'status', 'progress', 'paused_reason', 'paused_at',
        'resumed_at', 'total_paused_days',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'paused_at'  => 'datetime',
        'resumed_at' => 'datetime',
        'progress'   => 'decimal:2',
    ];

    public const STATUS_LABELS = [
        'not_started'       => '未开始',
        'in_progress'       => '进行中',
        'completed'         => '正常结案',
        'overdue'           => '延期结案',
        'suspended'         => '暂停',
        'overdue_completed' => '逾期完成',
    ];

    public const STATUS_COLORS = [
        'not_started'       => '#6c757d',
        'in_progress'       => '#ffc107',
        'completed'         => '#28a745',
        'overdue'           => '#fd7e14',
        'suspended'         => '#6f42c1',
        'overdue_completed' => '#0dcaf0',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('start_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CollabDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProjectLog::class)->latest();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProjectMessage::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#6c757d';
    }

    /** 参与部门：由任务所属部门推导（与原 PM 一致） */
    public function participatingDepartments()
    {
        $ids = $this->tasks()->pluck('department_id')->filter()->unique();

        return \App\Core\Models\Department::whereIn('id', $ids)->orderBy('id')->get();
    }

    /** 项目成员：创建人 + 任务执行人/审核人（与原 PM 一致） */
    public function participants()
    {
        $ids = $this->tasks()->get(['executor_id', 'auditor_id'])->flatMap(fn ($t) => [$t->executor_id, $t->auditor_id])
            ->filter()->unique()->prepend($this->creator_id)->unique()->values();

        return User::whereIn('id', $ids)->orderBy('id')->get();
    }

    public function isMember(User $user): bool
    {
        if ($user->id === $this->creator_id || $user->hasAnyRole(['core.super_admin', 'pm.project_manager'])) {
            return true;
        }

        return $this->tasks()->where(function ($q) use ($user) {
            $q->where('executor_id', $user->id)->orWhere('auditor_id', $user->id);
        })->exists();
    }

    public function calculateProgress(): float
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return (float) $this->progress;
        }
        $done = $this->tasks()->whereIn('status', ['completed'])->count();

        return round($done / $total * 100, 2);
    }

    /** 项目统计（概览页 6 格） */
    public function taskStats(): array
    {
        $tasks = $this->tasks()->get();

        return [
            'total_tasks'      => $tasks->count(),
            'completed_tasks'  => $tasks->where('status', 'completed')->count(),
            'pending_tasks'    => $tasks->where('status', 'pending')->count(),
            'for_audit_tasks'  => $tasks->where('status', 'for_audit')->count(),
            'rejected_tasks'   => $tasks->where('status', 'rejected')->count(),
            'overdue_tasks'    => $tasks->where('status', 'overdue')->count(),
        ];
    }
}
