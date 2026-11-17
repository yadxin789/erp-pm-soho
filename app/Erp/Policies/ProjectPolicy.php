<?php

namespace App\Erp\Policies;

use App\Erp\Models\Project\Project;
use App\Core\Models\User;

/**
 * 项目权限
 *
 * 规则（与参考目录一致，但「看全部」只给系统管理员）：
 * - 进入模块：admin / project_manager / viewer（即项目部全部岗位）
 * - 查看项目 / 查看统计：只有自己参与的项目（创建人、负责人、任一任务执行人或审核人）
 * - 创建项目：admin 或 project_manager
 * - 编辑 / 删除 / 暂停 / 恢复 / 变更：项目创建人或 admin
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        // 管理员只进后台；业务侧任何角色只要参与了项目就能进（参与判定在 RoleScope 与下方 view 中）
        return ! $user->hasRole('admin');
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $project->isParticipant($user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'project_manager']);
    }

    public function update(User $user, Project $project): bool
    {
        return $project->canEdit($user->id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->canDelete($user->id);
    }

    public function pause(User $user, Project $project): bool
    {
        return $project->canPause($user->id);
    }

    public function resume(User $user, Project $project): bool
    {
        return $project->canResume($user->id);
    }

    public function change(User $user, Project $project): bool
    {
        return $project->canChange($user->id);
    }

    /** 查看项目组聊天室 / 协同文档 / 电话会议 */
    public function collaborate(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
