<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * 项目履历（项目状态、任务状态、文件流转、暂停/恢复等操作的留痕）
 */
class ProjectLog extends Model
{
    protected $table = 'project_logs';

    protected $fillable = ['project_id', 'task_id', 'user_id', 'action', 'description', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public const ACTIONS = [
        'created' => '创建项目',
        'updated' => '编辑项目',
        'status' => '项目状态变更',
        'pause' => '暂停项目',
        'resume' => '恢复项目',
        'change' => '项目变更',
        'task_created' => '新增任务',
        'task_updated' => '编辑任务',
        'task_status' => '任务状态变更',
        'file_upload' => '上传文件',
        'file_approve' => '审核通过',
        'file_reject' => '审核驳回',
        'file_update' => '更新文件',
        'extension_apply' => '申请延期',
        'extension_approve' => '同意延期',
        'extension_reject' => '驳回延期',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? (string) $this->action;
    }

    /**
     * 写一条履历。失败不影响主流程。
     */
    public static function record(int $projectId, string $action, string $description = '', array $meta = []): void
    {
        try {
            static::create([
                'project_id' => $projectId,
                'task_id' => $meta['task_id'] ?? null,
                'user_id' => auth()->check() ? auth()->id() : null,
                'action' => $action,
                'description' => $description,
                'meta' => $meta ?: null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('项目履历写入失败: ' . $e->getMessage(), ['project_id' => $projectId, 'action' => $action]);
        }
    }
}
