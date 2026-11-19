<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * P2-1：补齐「路由需要但无任何角色持有」的操作级权限点。
 *
 * 背景：SOHO 有 8 个权限点由历史 seeder 建出但从未授予任何角色，导致对应页面只有
 * super_admin 能进（拣货 / 打包 / 复核任务、运费单、推单、采购审批、纠纷、换货、税率）。
 * 这里按岗位语义授予，裸名角色与 `soho.*` 前缀角色同步，保持两套命名等价。
 *
 * 幂等：已授予的跳过；角色不存在则跳过。
 */
return new class extends Migration
{
    private const GRANTS = [
        'pick.task.view' => ['picker'],
        'pack.task.view' => ['packer'],
        'check.task.view' => ['checker'],
        'shipment.view' => ['logistics_specialist', 'order_manager'],
        'shipment.push' => ['logistics_specialist'],
        'procurement.approval' => ['procurement_manager'],
        'dispute.handle' => ['aftersale_manager', 'customer_service'],
        'replacement.create' => ['aftersale_manager'],
        'tax_rate.view' => ['finance_manager', 'finance_staff'],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::GRANTS as $permissionName => $roleNames) {
            $permission = Permission::findOrCreate($permissionName, 'web');

            foreach ($roleNames as $roleName) {
                foreach ([$roleName, 'soho.' . $roleName] as $candidate) {
                    $role = Role::where('name', $candidate)->where('guard_name', 'web')->first();
                    if (! $role) {
                        continue;
                    }
                    if (! $role->hasPermissionTo($permissionName)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::GRANTS as $permissionName => $roleNames) {
            $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();
            if (! $permission) {
                continue;
            }
            foreach ($roleNames as $roleName) {
                foreach ([$roleName, 'soho.' . $roleName] as $candidate) {
                    $role = Role::where('name', $candidate)->where('guard_name', 'web')->first();
                    if ($role && $role->hasPermissionTo($permissionName)) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
