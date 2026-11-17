<?php

namespace App\Erp\Policies;

use App\Erp\Models\Project\ProjectTask;
use App\Core\Models\User;

/**
 * 任务权限
 *
 * 除「管理员看全部」外，全部按任务上的指派关系判定（执行人 / 审核人 / 项目创建人），
 * 与参考目录 TaskPolicy 同构。
 */
class TaskPolicy
{
    public function view(User $user, ProjectTask $task): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ((int) $task->project?->creator_id === $user->id) {
            return true;
        }

        return (int) $task->executor_id === $user->id || (int) $task->auditor_id === $user->id;
    }

    public function create(User $user, ?int $projectId = null): bool
    {
        return $user->hasAnyRole(['admin', 'project_manager']);
    }

    public function update(User $user, ProjectTask $task): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $task->project?->creator_id === $user->id;
    }

    public function delete(User $user, ProjectTask $task): bool
    {
        return $this->update($user, $task);
    }

    /** 上传交付文件（执行人本人） */
    public function uploadFile(User $user, ProjectTask $task): bool
    {
        return $task->canUploadFile($user->id);
    }

    /** 审核（审核人本人，且不能自审） */
    public function audit(User $user, ProjectTask $task): bool
    {
        return $task->canAudit($user->id);
    }

    /** 申请延期（执行人本人） */
    public function applyExtension(User $user, ProjectTask $task): bool
    {
        return $task->canApplyExtension($user->id);
    }

    /** 更新已完成文件的版本 */
    public function updateFile(User $user, ProjectTask $task): bool
    {
        return $task->canUpdateFile($user->id);
    }
}
