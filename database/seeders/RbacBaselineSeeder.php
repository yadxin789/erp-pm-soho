<?php

namespace Database\Seeders;

use App\Core\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * TC-07 角色与权限前缀化重灌。
 *
 * 输入：docs/rbac/baseline-rbac.json（由 database/scripts/rbac_extract.php 从三套源库真实导出）
 *      database/seeders/data/pm-permissions.php（PM 新建权限目录）
 *
 * 规则：
 *   ERP  原 "create purchase orders" → erp.purchase_orders.create
 *   SOHO 原 "after_sale.approve"     → soho.after_sale.approve
 *   PM   新建                         → pm.project.view 等（见 pm-permissions.php）
 *   角色统一 → {module}.{原角色名}
 *   guard 统一 web（erp/pm/soho 原来就是 web，只有 pm 的 roles 表没存 guard）
 */
class RbacBaselineSeeder extends Seeder
{
    private array $stats = [];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 重灌前清空（仅本 seeder 管辖的三模块角色与权限）
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $baseline = json_decode(file_get_contents(base_path('docs/rbac/baseline-rbac.json')), true);
        if (! $baseline) {
            throw new \RuntimeException('缺少 docs/rbac/baseline-rbac.json，请先运行 database/scripts/rbac_extract.php');
        }

        $this->importErp($baseline['sources']['erp']);
        $this->importSoho($baseline['sources']['soho']);
        $this->importPm();
        $this->createCoreRoles();

        // 给验收账号挂角色（生产由迁移映射，见 B-05）
        $this->assignDemoUsers();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('RBAC 重灌完成：');
        foreach ($this->stats as $k => $v) {
            $this->command?->info("  {$k}: {$v}");
        }
    }

    // ------------------------------------------------------------------

    private function importErp(array $src): void
    {
        $permNames = [];
        foreach ($src['permissions'] as $raw) {
            $permNames[$this->erpName($raw)] = $raw;
        }

        if (count($permNames) !== count($src['permissions'])) {
            $this->command?->warn('ERP 权限名转换出现冲突：'.count($src['permissions']).' → '.count($permNames));
        }

        foreach ($permNames as $name => $raw) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $raw, 'group' => 'erp', 'display' => true]
            );
        }

        $roleCount = 0;
        foreach ($src['roles'] as $role) {
            $core = Role::firstOrCreate(
                ['name' => 'erp.'.$role['name'], 'guard_name' => 'web'],
                ['display_name' => 'ERP · '.$role['name'], 'group' => 'erp', 'display' => true]
            );

            $target = [];
            foreach ($role['permissions'] as $raw) {
                $target[] = $this->erpName($raw);
            }
            $core->syncPermissions($this->existing(array_unique($target)));
            $roleCount++;
        }

        $this->stats['ERP'] = count($permNames).' 权限 / '.$roleCount.' 角色';
    }

    private function importSoho(array $src): void
    {
        $names = [];
        foreach ($src['permissions'] as $raw) {
            $names[] = 'soho.'.$raw;
        }

        foreach ($names as $i => $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $src['permissions'][$i], 'group' => 'soho', 'display' => true]
            );
        }

        $roleCount = 0;
        foreach ($src['roles'] as $role) {
            $core = Role::firstOrCreate(
                ['name' => 'soho.'.$role['name'], 'guard_name' => 'web'],
                ['display_name' => 'SOHO · '.$role['name'], 'group' => 'soho', 'display' => true]
            );

            $target = [];
            foreach ($role['permissions'] as $raw) {
                $target[] = 'soho.'.$raw;
            }
            $core->syncPermissions($this->existing(array_unique($target)));
            $roleCount++;
        }

        $this->stats['SOHO'] = count($names).' 权限 / '.$roleCount.' 角色';
    }

    private function importPm(): void
    {
        $spec = require database_path('seeders/data/pm-permissions.php');

        $names = [];
        foreach ($spec['catalog'] as $group => $actions) {
            foreach ($actions as $action => $label) {
                $names[] = 'pm.'.$group.'.'.$action;
                Permission::firstOrCreate(
                    ['name' => 'pm.'.$group.'.'.$action, 'guard_name' => 'web'],
                    ['display_name' => $label, 'group' => 'pm', 'display' => true]
                );
            }
        }

        $roleCount = 0;
        foreach ($spec['roles'] as $code => $allowed) {
            $core = Role::firstOrCreate(
                ['name' => 'pm.'.$code, 'guard_name' => 'web'],
                ['display_name' => 'PM · '.$code, 'group' => 'pm', 'display' => true]
            );

            if (in_array('@all', $allowed, true)) {
                $core->syncPermissions($this->existing($names));
            } else {
                $core->syncPermissions($this->existing(array_map(fn ($p) => 'pm.'.$p, $allowed)));
            }
            $roleCount++;
        }

        $this->stats['PM'] = count($names).' 权限（新建） / '.$roleCount.' 角色';
    }

    private function createCoreRoles(): void
    {
        $corePerms = [
            'core.user.manage'         => '用户管理',
            'core.department.manage'   => '部门管理',
            'core.position.manage'     => '岗位管理',
            'core.role.manage'         => '角色权限管理',
            'core.module.manage'       => '模块授权管理',
            'core.notification.manage' => '通知模板管理',
            'core.log.view'            => '操作日志查看',
            'core.alert.manage'        => '系统告警处理',
        ];

        foreach ($corePerms as $name => $label) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $label, 'group' => 'core', 'display' => true]
            );
        }

        $role = Role::firstOrCreate(
            ['name' => 'core.super_admin', 'guard_name' => 'web'],
            ['display_name' => 'Core · 超级管理员', 'group' => 'core', 'display' => true]
        );
        $role->syncPermissions($this->existing(array_keys($corePerms)));

        $this->stats['Core'] = count($corePerms).' 权限 / 1 角色';
    }

    private function assignDemoUsers(): void
    {
        $map = [
            'admin'        => ['erp.admin', 'core.super_admin'],
            'pm_director'  => ['pm.project_manager'],
            'admin@soho.demo' => ['soho.tenant_admin'],
            'multi_module' => ['erp.sales_manager', 'soho.operation_manager'],
        ];

        foreach ($map as $identifier => $roles) {
            $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();
            if (! $user) {
                continue;
            }

            foreach ($roles as $roleName) {
                if (Role::where('name', $roleName)->exists()) {
                    $user->assignRole($roleName);
                }
            }
        }
    }

    // ------------------------------------------------------------------

    /** ERP 原始 "action resource" → {module}.{resource}.{action} */
    private function erpName(string $raw): string
    {
        $parts = preg_split('/\s+/', trim($raw), 2);
        $action = strtolower($parts[0] ?? 'view');
        $resource = strtolower(str_replace([' ', '-'], '_', $parts[1] ?? $action));

        return 'erp.'.$resource.'.'.$action;
    }

    /** 只取数据库里确实存在的权限名，避免不存在的名字抛异常 */
    private function existing(array $names): array
    {
        return Permission::whereIn('name', $names)->where('guard_name', 'web')->pluck('name')->all();
    }
}
