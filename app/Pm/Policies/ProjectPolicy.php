<?php

namespace App\Pm\Policies;

use App\Pm\Models\Project;
use App\Core\Models\User;

class ProjectPolicy
{
    /**
     * 查看项目详情 — 项目只属 PM 模块（D27）：PM 模块用户且与项目相关的人可看。
     * 跨模块管理员（erp.admin / soho.super_admin 等）不再直放，避免 ERP 侧出现项目入口。
     */
    public function view(User $user, Project $project): bool
    {
        if (! $this->inPmModule($user)) {
            return false;
        }
        if ($this->isPmAdmin($user)) {
            return true;
        }
        if ($user->id === $project->creator_id) {
            return true;
        }
        return $project->tasks()
            ->where(function ($q) use ($user) {
                $q->where('executor_id', $user->id)
                  ->orWhere('auditor_id', $user->id);
            })
            ->exists();
    }

    /** 是否属于 PM 模块（含 core.super_admin 全局口径） */
    private function inPmModule(User $user): bool
    {
        return $user->canEnterModule('pm') || $user->hasRole('core.super_admin');
    }

    /** PM 模块管理员（pm.admin / 原 admin 角色 / 项目负责人） */
    private function isPmAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'pm.admin']) || $user->isProjectManager();
    }

    /**
     * 创建项目 — 项目发起人或管理员
     */
    public function create(User $user): bool
    {
        return $this->inPmModule($user) && ($user->isProjectCreator() || $this->isPmAdmin($user));
    }

    /**
     * 编辑/更新项目 — 项目创建人或管理员
     */
    public function update(User $user, Project $project): bool
    {
        return $this->inPmModule($user) && ($user->id === $project->creator_id || $this->isPmAdmin($user));
    }

    /**
     * 删除项目 — 仅项目创建人或管理员
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->inPmModule($user) && ($user->id === $project->creator_id || $this->isPmAdmin($user));
    }

    /**
     * 暂停项目 — 使用 Project 模型的统一判断（角色 + 状态：必须是 InProgress/Overdue）
     */
    public function pause(User $user, Project $project): bool
    {
        return $project->canPause($user->id);
    }

    /**
     * 恢复项目 — 使用 Project 模型的统一判断（角色 + 状态：必须是 Suspended）
     */
    public function resume(User $user, Project $project): bool
    {
        return $project->canResume($user->id);
    }

    /**
     * 变更项目 — 使用 Project 模型的统一判断（角色 + 状态：非终态）
     */
    public function change(User $user, Project $project): bool
    {
        return $project->canChange($user->id);
    }
}
