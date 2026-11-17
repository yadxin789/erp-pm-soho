<?php

namespace App\Core\Services;

use App\Core\Models\ApprovalFlow;
use App\Core\Models\ApprovalInstance;
use App\Core\Models\ApprovalStep;
use App\Core\Models\ApprovalTask;
use App\Core\Models\Department;
use App\Core\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * 审批引擎（TC-11）：线性多级 + 并行同一步多人。
 *
 * 三源实情：
 *   ERP  —— approval_flows + approval_flow_steps + approval_tasks（步骤带 approver_type/value=角色 slug）
 *   SOHO —— approval_flows(flow_config.nodes[]) + approval_instances（节点写在 JSON 里，一次性审批）
 * 合并后统一按「流 → 有序步骤 → 实例 → 待办」四层，规则与两侧一致：
 *   同一步任一人同意即推进；驳回即整单驳回；发起可撤销；单据侧只需给出 module + document_type + document_id。
 */
class ApprovalService
{
    /**
     * 按单据类型取启用中的流（同模块可有多条，取最新创建的）。
     */
    public function flowFor(string $module, string $documentType): ?ApprovalFlow
    {
        return ApprovalFlow::query()
            ->forModule($module)
            ->enabled()
            ->where('document_type', $documentType)
            ->with('steps')
            ->latest('id')
            ->first();
    }

    /**
     * 发起审批。返回实例（含第一步待办）。
     */
    public function start(
        ApprovalFlow $flow,
        User $applicant,
        string $documentType,
        int $documentId,
        string $title,
        array $payload = []
    ): ApprovalInstance {
        if (! $flow->enabled) {
            throw new RuntimeException('审批流已停用：'.$flow->name);
        }
        if ($flow->steps->isEmpty()) {
            throw new RuntimeException('审批流没有配置步骤：'.$flow->name);
        }

        $existing = ApprovalInstance::query()
            ->forModule($flow->module)
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->where('status', ApprovalInstance::STATUS_PENDING)
            ->first();
        if ($existing) {
            throw new RuntimeException('该单据已有一条进行中的审批：'.$existing->title);
        }

        return DB::transaction(function () use ($flow, $applicant, $documentType, $documentId, $title, $payload) {
            $instance = ApprovalInstance::create([
                'approval_flow_id' => $flow->id,
                'module' => $flow->module,
                'document_type' => $documentType,
                'document_id' => $documentId,
                'title' => $title,
                'applicant_id' => $applicant->id,
                'current_step' => 1,
                'status' => ApprovalInstance::STATUS_PENDING,
                'submitted_at' => now(),
                'payload' => $payload,
            ]);

            $this->openStep($instance, $instance->flow->steps->first());

            return $instance->fresh('tasks');
        });
    }

    /**
     * 当前步骤的待审人（按 approver_type 解析）。角色命中多人则每人一条待办。
     */
    public function approversOf(ApprovalStep $step, ApprovalInstance $instance): Collection
    {
        $value = (string) $step->approver_value;

        return match ($step->approver_type) {
            ApprovalStep::TYPE_USER => User::where('id', (int) $value)->get(),
            ApprovalStep::TYPE_ROLE => $this->usersWithRole($value),
            ApprovalStep::TYPE_DEPARTMENT => $this->usersOfDepartment((int) $value),
            ApprovalStep::TYPE_APPLICANT => User::where('id', $instance->applicant_id)->get(),
            default => collect(),
        };
    }

    /**
     * 我的待办（按模块过滤，跨模块用户在各自模块里只看到自己的）。
     */
    public function pendingFor(User $user, ?string $module = null): Collection
    {
        return ApprovalTask::query()
            ->pending()
            ->when($module, fn ($q) => $q->where('module', $module))
            ->where('approver_id', $user->id)
            ->with(['instance.flow', 'instance.applicant'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 我发起的（含已结束）。
     */
    public function submittedBy(User $user, ?string $module = null): Collection
    {
        return ApprovalInstance::query()
            ->when($module, fn ($q) => $q->where('module', $module))
            ->where('applicant_id', $user->id)
            ->with(['flow', 'tasks.approver'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 同意。同一步还有别人未处理 → 其余置 skipped（任一同意即推进，与 ERP 一致）。
     */
    public function approve(ApprovalTask $task, User $user, ?string $comment = null): ApprovalInstance
    {
        $this->assertHandler($task, $user);

        return DB::transaction(function () use ($task, $user, $comment) {
            $task->update([
                'status' => ApprovalTask::STATUS_APPROVED,
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            $instance = $task->instance;
            ApprovalTask::where('approval_instance_id', $instance->id)
                ->where('step_order', $task->step_order)
                ->where('status', ApprovalTask::STATUS_PENDING)
                ->update(['status' => ApprovalTask::STATUS_SKIPPED, 'updated_at' => now()]);

            $next = $instance->flow->steps->firstWhere('step_order', '>', $task->step_order);
            if ($next) {
                $instance->update(['current_step' => $next->step_order]);
                $this->openStep($instance, $next);
            } else {
                $instance->update([
                    'current_step' => $task->step_order,
                    'status' => ApprovalInstance::STATUS_APPROVED,
                    'finished_at' => now(),
                ]);
            }

            return $instance->fresh(['tasks.approver', 'flow']);
        });
    }

    /**
     * 驳回：整单结束，剩余待办全部置 cancelled。
     */
    public function reject(ApprovalTask $task, User $user, ?string $comment = null): ApprovalInstance
    {
        $this->assertHandler($task, $user);

        return DB::transaction(function () use ($task, $user, $comment) {
            $task->update([
                'status' => ApprovalTask::STATUS_REJECTED,
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            $instance = $task->instance;
            ApprovalTask::where('approval_instance_id', $instance->id)
                ->where('status', ApprovalTask::STATUS_PENDING)
                ->update(['status' => ApprovalTask::STATUS_CANCELLED, 'updated_at' => now()]);

            $instance->update([
                'status' => ApprovalInstance::STATUS_REJECTED,
                'finished_at' => now(),
            ]);

            return $instance->fresh(['tasks.approver', 'flow']);
        });
    }

    /**
     * 发起人撤销（已结束的单子不能撤）。
     */
    public function cancel(ApprovalInstance $instance, User $user): ApprovalInstance
    {
        if ($instance->applicant_id !== $user->id && ! $user->is_admin) {
            throw new RuntimeException('只有发起人可以撤销');
        }
        if (! $instance->isPending()) {
            throw new RuntimeException('该审批已结束：'.$instance->statusLabel());
        }

        return DB::transaction(function () use ($instance) {
            ApprovalTask::where('approval_instance_id', $instance->id)
                ->where('status', ApprovalTask::STATUS_PENDING)
                ->update(['status' => ApprovalTask::STATUS_CANCELLED, 'updated_at' => now()]);

            $instance->update([
                'status' => ApprovalInstance::STATUS_CANCELLED,
                'finished_at' => now(),
            ]);

            return $instance->fresh(['tasks.approver', 'flow']);
        });
    }

    /**
     * 按单据拿当前实例（业务侧判断「这单能不能改」用）。
     */
    public function forDocument(string $module, string $documentType, int $documentId): ?ApprovalInstance
    {
        return ApprovalInstance::query()
            ->forModule($module)
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->latest('id')
            ->with(['flow', 'tasks.approver'])
            ->first();
    }

    private function openStep(ApprovalInstance $instance, ApprovalStep $step): void
    {
        $approvers = $this->approversOf($step, $instance);
        if ($approvers->isEmpty()) {
            throw new RuntimeException("步骤「{$step->name}」找不到审批人（{$step->approver_type}:{$step->approver_value}）");
        }

        foreach ($approvers as $approver) {
            ApprovalTask::create([
                'approval_instance_id' => $instance->id,
                'module' => $instance->module,
                'document_type' => $instance->document_type,
                'document_id' => $instance->document_id,
                'step_order' => $step->step_order,
                'step_name' => $step->name,
                'approver_id' => $approver->id,
                'status' => ApprovalTask::STATUS_PENDING,
            ]);
        }
    }

    private function assertHandler(ApprovalTask $task, User $user): void
    {
        if ($task->approver_id !== $user->id) {
            throw new RuntimeException('这条待办不属于当前账号');
        }
        if ($task->status !== ApprovalTask::STATUS_PENDING) {
            throw new RuntimeException('这条待办已处理：'.$task->statusLabel());
        }
    }

    /**
     * 角色名按「带模块前缀」解析：erp.admin / soho.operation_manager。
     * 兼容不带前缀的老角色名（先精确匹配，再补任意模块后缀匹配）。
     */
    public function usersWithRole(string $role): Collection
    {
        $names = [$role];
        if (! str_contains($role, '.')) {
            $names[] = 'erp.'.$role;
            $names[] = 'pm.'.$role;
            $names[] = 'soho.'.$role;
        }

        $ids = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', $names)
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id');

        return User::whereIn('id', $ids)->get();
    }

    /**
     * 部门审批人：部门全员（Core 的 departments 没有 leader 字段，统一走全员，任一人处理即推进）。
     */
    private function usersOfDepartment(int $departmentId): Collection
    {
        if (! Department::whereKey($departmentId)->exists()) {
            return collect();
        }

        return User::where('department_id', $departmentId)->get();
    }
}
