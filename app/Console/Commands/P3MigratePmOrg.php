<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * P3 数据迁移：把参考工程 PM 库（pm_db）中尚未进入合并站的部门与用户补齐。
 *
 * 规则（最小改动、可重复执行）：
 *  - 部门：按「名称」去重，缺失的补建，module=pm，code 统一 PM-<源code 大写>；已存在的不动。
 *  - 用户：按 username 去重，缺失的补建，module=pm，department_id 按部门名称映射，
 *    password / avatar / bio / wecom_* 原样继承，status 与 is_active 取源 is_active。
 *  - 角色：按源 role_id → 角色名 → pm.<name> 映射写入 model_has_roles，已存在则不重复。
 *
 * 用法：php artisan p3:migrate-pm-org [--apply]
 *  不带 --apply 为演练（只打印计划，不写库）。
 */
class P3MigratePmOrg extends Command
{
    protected $signature = 'p3:migrate-pm-org {--apply : 真正写库，缺省为演练}';

    protected $description = 'P3 校验：补齐 PM 源库(pm_db)缺失的部门与用户';

    /** 源 role_id → 合并站角色名 */
    private const ROLE_MAP = [
        1 => 'pm.admin',
        2 => 'pm.project_creator',
        3 => 'pm.project_manager',
        4 => 'pm.task_executor',
        5 => 'pm.task_auditor',
        6 => 'pm.support',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $this->line($apply ? '== 模式：APPLY（写库）==' : '== 模式：DRY-RUN（不写库）==');

        $srcDepartments = DB::table('pm_db.departments')->orderBy('id')->get();
        $srcUsers = DB::table('pm_db.users')->orderBy('id')->get();

        $deptPlan = [];
        foreach ($srcDepartments as $d) {
            $exists = DB::table('departments')->where('name', $d->name)->whereNull('deleted_at')->first();
            $deptPlan[] = [
                'source_id' => $d->id,
                'name' => $d->name,
                'code' => 'PM-' . strtoupper($d->code ?? ('DEPT' . $d->id)),
                'merged_id' => $exists->id ?? null,
                'action' => $exists ? 'keep' : 'create',
            ];
        }
        $createDepts = array_filter($deptPlan, fn ($r) => $r['action'] === 'create');
        $this->info('部门：源 ' . count($deptPlan) . ' 个，需新建 ' . count($createDepts) . ' 个');
        foreach ($deptPlan as $r) {
            $this->line(sprintf('  [%s] #%d %s → code=%s%s', $r['action'], $r['source_id'], $r['name'], $r['code'], $r['merged_id'] ? ' (合并站 #' . $r['merged_id'] . ')' : ''));
        }

        if ($apply && $createDepts) {
            foreach ($createDepts as $r) {
                $id = DB::table('departments')->insertGetId([
                    'code' => $r['code'],
                    'name' => $r['name'],
                    'short_name' => mb_substr($r['name'], 0, 10),
                    'description' => '由 PM 源库迁移（P3）',
                    'parent_id' => null,
                    'module' => 'pm',
                    'type' => 'department',
                    'sort_order' => $r['source_id'],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line("  新建部门 #{$id} {$r['name']}");
            }
        }

        // 源部门 id → 合并站部门 id（按名称解析，已建与既有一致处理）
        $deptIdMap = [];
        foreach ($deptPlan as $r) {
            $deptIdMap[$r['source_id']] = $r['merged_id'] ?: DB::table('departments')->where('name', $r['name'])->whereNull('deleted_at')->value('id');
        }

        $userPlan = [];
        foreach ($srcUsers as $u) {
            $exists = DB::table('users')->where('username', $u->username)->whereNull('deleted_at')->first();
            $userPlan[] = [
                'source_id' => $u->id,
                'username' => $u->username,
                'name' => $u->name,
                'dept_source' => $u->department_id,
                'dept_merged' => $deptIdMap[$u->department_id] ?? null,
                'role' => self::ROLE_MAP[$u->role_id] ?? null,
                'active' => (int) $u->is_active,
                'merged_id' => $exists->id ?? null,
                'action' => $exists ? 'keep' : 'create',
            ];
        }
        $createUsers = array_filter($userPlan, fn ($r) => $r['action'] === 'create');
        $noDept = array_filter($createUsers, fn ($r) => ! $r['dept_merged']);
        $noRole = array_filter($createUsers, fn ($r) => ! $r['role']);
        $this->info('用户：源 ' . count($userPlan) . ' 个，需新建 ' . count($createUsers) . ' 个；无部门映射 ' . count($noDept) . '，无角色映射 ' . count($noRole));

        if (! $apply) {
            foreach ($userPlan as $r) {
                $this->line(sprintf('  [%s] %-22s dept %s→%s role=%s active=%d', $r['action'], $r['username'], $r['dept_source'], $r['dept_merged'] ?? '-', $r['role'] ?? '-', $r['active']));
            }
            $this->warn('演练结束，未写库。加 --apply 执行。');

            return self::SUCCESS;
        }

        $created = 0;
        foreach ($srcUsers as $u) {
            if (DB::table('users')->where('username', $u->username)->whereNull('deleted_at')->exists()) {
                continue;
            }
            $id = DB::table('users')->insertGetId([
                'name' => $u->name,
                'real_name' => $u->name,
                'username' => $u->username,
                'email' => null,
                'password' => $u->password,
                'avatar' => $u->avatar ?? null,
                'bio' => $u->bio ?? null,
                'department_id' => $deptIdMap[$u->department_id] ?? null,
                'role_id' => $u->role_id,
                'status' => (int) $u->is_active,
                'is_active' => (int) $u->is_active,
                'is_admin' => 0,
                'module' => 'pm',
                'wecom_userid' => $u->wecom_userid ?? null,
                'wecom_bound_at' => $u->wecom_bound_at ?? null,
                'wecom_notify_enabled' => $u->wecom_notify_enabled ?? 1,
                'wecom_notify_settings' => $u->wecom_notify_settings ?? null,
                'created_at' => $u->created_at ?? now(),
                'updated_at' => now(),
            ]);
            $roleName = self::ROLE_MAP[$u->role_id] ?? null;
            if ($roleName) {
                $roleId = DB::table('roles')->where('name', $roleName)->value('id');
                if ($roleId && ! DB::table('model_has_roles')->where('role_id', $roleId)->where('model_id', $id)->where('model_type', 'App\\Core\\Models\\User')->exists()) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $roleId,
                        'model_type' => 'App\\Core\\Models\\User',
                        'model_id' => $id,
                    ]);
                }
            }
            $created++;
            $this->line("  新建用户 #{$id} {$u->username}（{$u->name}）");
        }
        $this->info("完成：新建用户 {$created} 个，新建部门 " . count($createDepts) . ' 个');

        return self::SUCCESS;
    }
}
