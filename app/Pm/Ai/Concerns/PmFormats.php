<?php

namespace App\Pm\Ai\Concerns;

use App\Pm\States\Project\Completed as ProjectCompleted;
use App\Pm\States\Project\InProgress as ProjectInProgress;
use App\Pm\States\Project\NotStarted as ProjectNotStarted;
use App\Pm\States\Project\Overdue as ProjectOverdue;
use App\Pm\States\Project\OverdueCompleted as ProjectOverdueCompleted;
use App\Pm\States\Project\Suspended as ProjectSuspended;
use App\Pm\States\Task\Completed as TaskCompleted;
use App\Pm\States\Task\ForAudit as TaskForAudit;
use App\Pm\States\Task\InProgress as TaskInProgress;
use App\Pm\States\Task\Overdue as TaskOverdue;
use App\Pm\States\Task\OverdueCompleted as TaskOverdueCompleted;
use App\Pm\States\Task\Pending as TaskPending;
use App\Pm\States\Task\Rejected as TaskRejected;

trait PmFormats
{
    /**
     * 将状态对象/类名转为可读中文标签
     */
    protected function taskStateLabel(mixed $status): string
    {
        $class = is_string($status) ? $status : get_class($status);

        return match ($class) {
            TaskPending::class => '待开始',
            TaskInProgress::class => '进行中',
            TaskForAudit::class => '待审核',
            TaskCompleted::class => '已完成',
            TaskOverdue::class => '已逾期',
            TaskOverdueCompleted::class => '逾期完成',
            TaskRejected::class => '已驳回',
            default => $class,
        };
    }

    protected function projectStateLabel(mixed $status): string
    {
        $class = is_string($status) ? $status : get_class($status);

        return match ($class) {
            ProjectNotStarted::class => '未开始',
            ProjectInProgress::class => '进行中',
            ProjectOverdue::class => '已逾期',
            ProjectCompleted::class => '已完成',
            ProjectOverdueCompleted::class => '逾期完成',
            ProjectSuspended::class => '已暂停',
            default => $class,
        };
    }
}
