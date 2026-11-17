<?php

namespace App\Soho\Console\Commands;

use App\Core\Models\ApprovalInstance;
use App\Core\Models\ApprovalTask;
use App\Core\Models\User;
use App\Core\Services\ApprovalService;
use Illuminate\Console\Command;

/**
 * 审批流程验收命令（TC-11 重写）。
 *
 * 原 SOHO 版本依赖 App\Soho\Models\Tenant（该模型未随多租户改造迁入，命令已跑不通），
 * 现改为直接打 Core 审批引擎：真实走完 ERP 销售订单流与 SOHO 商品发布流，
 * 覆盖「逐级同意 → 通过」「第一步驳回 → 整单驳回」「发起人撤销」三条链路。
 *
 * 命令名与用途保持原样（B-01 的 TC-11 完成标准要求 soho:test-approval-flow 可用）。
 */
class TestApprovalFlow extends Command
{
    protected $signature = 'soho:test-approval-flow {--module= : 只跑 erp 或 soho}';

    protected $description = '审批流程验收：发起 → 逐级同意 → 通过；并验证驳回与撤销';

    public function handle(ApprovalService $service): int
    {
        // 先清掉上一次异常中断留下的验收痕迹，避免「该单据已有进行中的审批」
        foreach (ApprovalInstance::where('title', 'like', '[命令验收]%')->get() as $stale) {
            ApprovalTask::where('approval_instance_id', $stale->id)->delete();
            $stale->delete();
        }

        $only = $this->option('module');
        $fail = 0;

        $cases = [
            'erp' => ['sales_order', 'admin', '销售订单审批流', 9001],
            'soho' => ['product_publish', 'admin@soho.demo', '商品发布审批流', 9101],
        ];

        foreach ($cases as $module => [$documentType, $applicantName, $label, $documentId]) {
            if ($only && $only !== $module) {
                continue;
            }

            $this->line("<info>=== {$module} · {$label} ===</info>");

            $flow = $service->flowFor($module, $documentType);
            if (! $flow) {
                $this->error("找不到启用中的审批流：{$module}/{$documentType}");
                $fail++;

                continue;
            }

            $applicant = User::where('username', $applicantName)->orWhere('email', $applicantName)->first();
            if (! $applicant) {
                $this->error("找不到发起人：{$applicantName}");
                $fail++;

                continue;
            }

            $this->line('  流程：'.$flow->steps->map(fn ($s) => $s->step_order.'.'.$s->name.'('.$s->approver_value.')')->implode(' → '));

            try {
                $instance = $service->start($flow, $applicant, $documentType, $documentId, "[命令验收] {$label}", ['source' => 'soho:test-approval-flow']);
            } catch (\Throwable $e) {
                $this->error('  发起失败：'.$e->getMessage());
                $fail++;

                continue;
            }

            $this->line("  发起：instance#{$instance->id} 状态={$instance->status} 当前步={$instance->current_step}");

            foreach ($flow->steps as $step) {
                $tasks = ApprovalTask::where('approval_instance_id', $instance->id)
                    ->where('step_order', $step->step_order)
                    ->where('status', ApprovalTask::STATUS_PENDING)
                    ->get();

                if ($tasks->isEmpty()) {
                    $this->error("  第 {$step->step_order} 步没有待办，链路断了");
                    $fail++;

                    continue 2;
                }

                $this->line("  第 {$step->step_order} 步（{$step->name}）待办 ".$tasks->count().' 条，处理人 '.$tasks->pluck('approver_id')->implode(','));

                $task = $tasks->first();
                $service->approve($task, $task->approver, '命令验收：同意');
                $fresh = $instance->fresh();
                $this->line("  → 处理后状态={$fresh->status} 当前步={$fresh->current_step}");
            }

            if ($instance->fresh()->status !== ApprovalInstance::STATUS_APPROVED) {
                $this->error('  走完所有步骤但状态不是 approved');
                $fail++;
            } else {
                $this->info('  ✔ 全部步骤通过，实例已 approved');
            }

            // 驳回分支：另起一单，第一步驳回
            $second = $service->start($flow, $applicant, $documentType, $documentId + 1, '[命令验收] 驳回分支');
            $task = ApprovalTask::where('approval_instance_id', $second->id)->where('status', ApprovalTask::STATUS_PENDING)->first();
            $service->reject($task, $task->approver, '命令验收：驳回');
            $second = $second->fresh(['tasks']);
            $remaining = $second->tasks->where('status', ApprovalTask::STATUS_PENDING)->count();
            if ($second->status === ApprovalInstance::STATUS_REJECTED && $remaining === 0) {
                $this->info('  ✔ 驳回后整单 rejected，剩余待办清零');
            } else {
                $this->error('  驳回分支异常：status='.$second->status.' pending='.$remaining);
                $fail++;
            }

            // 撤销分支
            $third = $service->start($flow, $applicant, $documentType, $documentId + 2, '[命令验收] 撤销分支');
            $service->cancel($third, $applicant);
            if ($third->fresh()->status === ApprovalInstance::STATUS_CANCELLED) {
                $this->info('  ✔ 发起人撤销成功，状态 cancelled');
            } else {
                $this->error('  撤销分支异常：status='.$third->fresh()->status);
                $fail++;
            }

            // 清理本次验收造的实例，避免污染演示数据
            foreach ([$instance, $second, $third] as $item) {
                ApprovalTask::where('approval_instance_id', $item->id)->delete();
                ApprovalInstance::whereKey($item->id)->delete();
            }
            $this->line('  （已清理本次 3 条验收实例）');
        }

        $this->newLine();
        $this->line($fail === 0 ? '<info>全部通过</info>' : "<error>失败 {$fail} 项</error>");

        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }
}
