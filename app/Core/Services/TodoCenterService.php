<?php

namespace App\Core\Services;

use App\Core\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * 我的待办（P2-2）——跨模块聚合，**只读**，不合并表、不改各模块业务逻辑。
 *
 * 数据源（按模块）：
 *  - core：approval_tasks（审批我的任务）+ approval_instances（我发起且处理中）
 *  - erp ：erp_approval_tasks（审批我的任务）
 *  - pm  ：tasks（我执行 / 我审核）
 *  - soho：soho_approval_instances、soho_inventory_adjust_approvals、soho_procurement_approvals、
 *          soho_finance_diff_approvals、soho_order_cancel_approvals、soho_product_publish_approvals、
 *          soho_refund_flows
 *
 * 每条统一成：module / category / title / meta / url / at。
 */
class TodoCenterService
{
    /** 每类最多取多少条（页面只做「待我处理」的快速入口，不做全量报表） */
    private const LIMIT = 50;

    /**
     * @return array<int, array{module:string,label:string,items:list<array<string,mixed>>}>
     */
    public function groups(User $user): array
    {
        $groups = [];

        $groups[] = ['module' => 'core', 'label' => 'Core 审批', 'items' => $this->coreItems($user)];

        // 业务模块分组只在用户可进入该模块时出现，避免「看得到却点不进去」
        if ($this->canEnter($user, 'erp')) {
            $groups[] = ['module' => 'erp', 'label' => 'ERP 审批', 'items' => $this->erpItems($user)];
        }
        if ($this->canEnter($user, 'pm')) {
            $groups[] = ['module' => 'pm', 'label' => 'PM 任务', 'items' => $this->pmItems($user)];
        }
        if ($this->canEnter($user, 'soho')) {
            $groups[] = ['module' => 'soho', 'label' => 'SOHO 审批', 'items' => $this->sohoItems($user)];
        }

        return $groups;
    }

    /** 模块准入：D15 准入链（users.module → module_access → department.module），超管全放行 */
    private function canEnter(User $user, string $module): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        return method_exists($user, 'canEnterModule') && $user->canEnterModule($module);
    }

    /** 汇总：总数 + 各模块数量 + 可见模块清单 */
    public function summary(array $groups): array
    {
        $counts = [];
        $total = 0;
        foreach ($groups as $g) {
            $counts[$g['module']] = count($g['items']);
            $total += count($g['items']);
        }

        return [
            'total' => $total,
            'counts' => $counts,
            'modules' => array_map(fn ($g) => $g['module'], $groups),
        ];
    }

    // ---------------------------------------------------------------- core

    private function coreItems(User $user): array
    {
        $items = [];

        if (Schema::hasTable('approval_tasks') && $this->canOpen('core.approvals.show')) {
            $rows = DB::table('approval_tasks as t')
                ->join('approval_instances as i', 'i.id', '=', 't.approval_instance_id')
                ->where('t.approver_id', $user->id)
                ->where('t.status', 'pending')
                ->orderByDesc('t.created_at')
                ->limit(self::LIMIT)
                ->get(['t.id', 't.step_name', 't.created_at', 'i.id as instance_id', 'i.title', 'i.module', 'i.document_type']);

            foreach ($rows as $r) {
                $items[] = [
                    'module' => 'core',
                    'category' => '待我审批',
                    'title' => $r->title ?: ('单据 #'.$r->instance_id),
                    'meta' => trim(($r->step_name ? $r->step_name.'　' : '').($r->module ? strtoupper($r->module).' / ' : '').($r->document_type ?? '')),
                    'url' => Route::has('core.approvals.show') ? route('core.approvals.show', $r->instance_id) : null,
                    'at' => $this->toCarbon($r->created_at),
                ];
            }
        }

        if (Schema::hasTable('approval_instances') && $this->canOpen('core.approvals.show')) {
            $rows = DB::table('approval_instances')
                ->where('applicant_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->orderByDesc('submitted_at')
                ->limit(self::LIMIT)
                ->get(['id', 'title', 'module', 'status', 'submitted_at']);

            foreach ($rows as $r) {
                $items[] = [
                    'module' => 'core',
                    'category' => '我发起（处理中）',
                    'title' => $r->title ?: ('单据 #'.$r->id),
                    'meta' => ($r->module ? strtoupper($r->module).' / ' : '').'状态 '.$r->status,
                    'url' => Route::has('core.approvals.show') ? route('core.approvals.show', $r->id) : null,
                    'at' => $this->toCarbon($r->submitted_at),
                ];
            }
        }

        return $items;
    }

    // ----------------------------------------------------------------- erp

    private function erpItems(User $user): array
    {
        if (! Schema::hasTable('erp_approval_tasks') || ! $this->canOpen('app.approvals.my-tasks')) {
            return [];
        }

        $rows = DB::table('erp_approval_tasks')
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(self::LIMIT)
            ->get(['id', 'document_type', 'document_id', 'step_name', 'created_at']);

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'module' => 'erp',
                'category' => '待我审批',
                'title' => ($r->document_type ?: '单据').' #'.$r->document_id,
                'meta' => $r->step_name ?: '',
                'url' => Route::has('app.approvals.my-tasks') ? route('app.approvals.my-tasks') : null,
                'at' => $this->toCarbon($r->created_at),
            ];
        }

        return $items;
    }

    // ------------------------------------------------------------------ pm

    private function pmItems(User $user): array
    {
        if (! Schema::hasTable('tasks') || ! $this->canOpen('pm.task.my-tasks')) {
            return [];
        }

        $items = [];

        $rows = DB::table('tasks')
            ->where('executor_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNull('deleted_at')
            ->orderBy('end_date')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'status', 'end_date', 'is_overdue']);

        foreach ($rows as $r) {
            $items[] = [
                'module' => 'pm',
                'category' => $r->is_overdue ? '我执行（已逾期）' : '我执行',
                'title' => $r->name,
                'meta' => '状态 '.$r->status.($r->end_date ? '　截止 '.$r->end_date : ''),
                'url' => Route::has('pm.task.my-tasks') ? route('pm.task.my-tasks') : null,
                'at' => $this->toCarbon($r->end_date),
            ];
        }

        $rows = DB::table('tasks')
            ->where('auditor_id', $user->id)
            ->whereIn('status', ['pending_audit', 'submitted', 'pending'])
            ->whereNull('deleted_at')
            ->orderBy('end_date')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'status', 'end_date']);

        foreach ($rows as $r) {
            $items[] = [
                'module' => 'pm',
                'category' => '待我审核',
                'title' => $r->name,
                'meta' => '状态 '.$r->status,
                'url' => Route::has('pm.task.my-audit-tasks') ? route('pm.task.my-audit-tasks') : null,
                'at' => $this->toCarbon($r->end_date),
            ];
        }

        return $items;
    }

    // ---------------------------------------------------------------- soho

    private function sohoItems(User $user): array
    {
        $items = [];

        // 通用审批实例（当前待处理）
        if (Schema::hasTable('soho_approval_instances') && $this->canOpen('soho.approval_instances.index')) {
            $rows = DB::table('soho_approval_instances')
                ->whereIn('status', ['pending', 'processing'])
                ->orderByDesc('submitted_at')
                ->limit(self::LIMIT)
                ->get(['id', 'title', 'biz_type', 'biz_no', 'status', 'submitted_by', 'submitted_at']);

            foreach ($rows as $r) {
                $items[] = [
                    'module' => 'soho',
                    'category' => (int) $r->submitted_by === (int) $user->id ? '我发起（处理中）' : '待审批实例',
                    'title' => $r->title ?: ($r->biz_no ?: '单据 #'.$r->id),
                    'meta' => trim(($r->biz_type ? $r->biz_type.'　' : '').'状态 '.$r->status),
                    'url' => Route::has('soho.approval_instances.index') ? route('soho.approval_instances.index') : null,
                    'at' => $this->toCarbon($r->submitted_at),
                ];
            }
        }

        // 域内审批队列（表结构不同，逐表适配）
        $queues = [
            [
                'table' => 'soho_inventory_adjust_approvals',
                'title' => fn ($r) => trim(($r->product_name ?: '库存调整').'　'.($r->order_no ?: '')),
                'meta' => fn ($r) => trim('数量 '.$r->quantity.'　'.($r->reason ?: '')),
                'at' => 'submitted_at',
                'route' => 'soho.approval.inventory-adjust.index',
            ],
            [
                'table' => 'soho_order_cancel_approvals',
                'title' => fn ($r) => trim(($r->product_name ?: '订单取消').'　'.($r->order_no ?: '')),
                'meta' => fn ($r) => trim('数量 '.$r->quantity.'　'.($r->reason ?: '')),
                'at' => 'submitted_at',
                'route' => 'soho.approval.order-cancel.index',
            ],
            [
                'table' => 'soho_product_publish_approvals',
                'title' => fn ($r) => trim(($r->product_name ?: '商品上架').'　'.($r->order_no ?: '')),
                'meta' => fn ($r) => trim(($r->reason ?: '') ?: '待审批'),
                'at' => 'submitted_at',
                'route' => 'soho.approval.product-publish.index',
            ],
            [
                'table' => 'soho_procurement_approvals',
                'title' => fn ($r) => trim(($r->title ?: '采购审批').'　'.($r->approval_no ?: '')),
                'meta' => fn ($r) => trim(($r->type ?: '').'　状态 '.$r->status),
                'at' => 'submitted_at',
                'route' => 'soho.procurement.approvals.index',
            ],
            [
                'table' => 'soho_finance_diff_approvals',
                'title' => fn ($r) => trim(($r->doc_no ?: '财务差异').'　'.($r->applicant_name ?: '')),
                'meta' => fn ($r) => trim('差异 '.$r->diff_amount.'　'.($r->type ?: '')),
                'at' => 'created_at',
                'route' => 'soho.approval.finance-diff.index',
            ],
            [
                'table' => 'soho_refund_flows',
                'title' => fn ($r) => trim(($r->refund_no ?: '退款').'　'.($r->order_no ?: '')),
                'meta' => fn ($r) => trim('金额 '.$r->amount.'　状态 '.$r->status),
                'at' => 'created_at',
                'route' => 'soho.approval.refund.index',
            ],
        ];

        foreach ($queues as $q) {
            // 用户对该审批队列无权时整队不出，避免「看得到却点不进去」
            if (! Schema::hasTable($q['table']) || ! $this->canOpen($q['route'])) {
                continue;
            }
            $rows = DB::table($q['table'])
                ->whereIn('status', ['pending', 'processing', '待审批'])
                ->orderByDesc($q['at'])
                ->limit(self::LIMIT)
                ->get();

            foreach ($rows as $r) {
                $url = Route::has($q['route']) ? route($q['route']) : null;
                $items[] = [
                    'module' => 'soho',
                    'category' => '待审批',
                    'title' => $q['title']($r),
                    'meta' => $q['meta']($r),
                    'url' => $url,
                    'at' => $this->toCarbon($r->{$q['at']} ?? null),
                ];
            }
        }

        return $items;
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * 「前往」目标是否对当前用户可打开（不越权、不出现 403/302）。
     *
     * 只按路由上挂的权限中间件判定（permission:xxx / CheckPermission:xxx）；
     * 角色白名单（soho_role:xxx）不在此裁决，避免与权限点重复决策。
     */
    private function canOpen(string $routeName): bool
    {
        if (! Route::has($routeName)) {
            return false;
        }

        $route = Route::getRoutes()->getByName($routeName);
        if (! $route) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $permission = null;

            if (str_starts_with($middleware, 'permission:')) {
                $permission = substr($middleware, strlen('permission:'));
            } elseif (str_contains($middleware, 'CheckPermission')) {
                $parts = explode(':', $middleware, 2);
                $permission = $parts[1] ?? '';
            }

            if ($permission === null) {
                continue;
            }

            $permissions = array_filter(array_map('trim', explode('|', $permission)));
            if ($permissions && ! can_perm(array_values($permissions))) {
                return false;
            }
        }

        return true;
    }
}

