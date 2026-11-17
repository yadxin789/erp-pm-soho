<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Core\Models\UserModuleAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TC-03 / TC-04 验证用种子：三套登录凭据形态 + 部门兜底 + 停用账号 + 多模块账号。
 * 生产数据走 P5 迁移，本 seeder 只用于开发环境验收。
 */
class CoreLoginDemoSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'PM', 'name' => '项目管理部', 'module' => 'pm', 'type' => 'project', 'sort_order' => 1],
            ['code' => 'FIN', 'name' => '财务部', 'module' => 'erp', 'type' => null, 'sort_order' => 2],
            ['code' => 'OPS', 'name' => '运营部', 'module' => 'soho', 'type' => null, 'sort_order' => 3],
            ['code' => 'ADM', 'name' => '系统后台管理部', 'module' => 'core', 'type' => null, 'sort_order' => 4],
        ];

        $deptIds = [];
        foreach ($departments as $d) {
            $deptIds[$d['code']] = Department::updateOrCreate(
                ['code' => $d['code']],
                $d + ['status' => 1]
            )->id;
        }

        // 1) ERP 形态：erp 的 name 兼作登录名，有邮箱
        User::updateOrCreate(['username' => 'admin'], [
            'name'          => 'admin',
            'real_name'     => '系统管理员',
            'email'         => 'admin@erp.demo',
            'password'      => Hash::make('123456'),
            'department_id' => $deptIds['FIN'],
            'module'        => 'erp',
            'status'        => 1,
            'is_admin'      => true,
        ]);

        // 2) PM 形态：纯 username，无邮箱（验证"无 email 也能登录"）
        User::updateOrCreate(['username' => 'pm_director'], [
            'name'          => '项目监督人',
            'real_name'     => '项目监督人',
            'email'         => null,
            'password'      => Hash::make('123456'),
            'department_id' => $deptIds['PM'],
            'module'        => 'pm',
            'status'        => 1,
        ]);

        // 3) SOHO 形态：无 username，靠 department.module 兜底解析到 soho
        User::updateOrCreate(['email' => 'admin@soho.demo'], [
            'name'          => '电商管理员',
            'real_name'     => '电商管理员',
            'username'      => null,
            'password'      => Hash::make('123456'),
            'department_id' => $deptIds['OPS'],
            'module'        => null,
            'status'        => 1,
            'tenant_id'     => 1,
        ]);

        // 4) 停用账号：status=0 必须被拒
        User::updateOrCreate(['username' => 'disabled_user'], [
            'name'          => '停用账号',
            'email'         => 'disabled@demo.local',
            'password'      => Hash::make('123456'),
            'department_id' => $deptIds['ADM'],
            'module'        => 'core',
            'status'        => 0,
        ]);

        // 5) 多模块账号：users.module=erp + 例外授权 soho → 登录后应回门户
        $multi = User::updateOrCreate(['username' => 'multi_module'], [
            'name'          => '跨模块用户',
            'email'         => 'multi@demo.local',
            'password'      => Hash::make('123456'),
            'department_id' => $deptIds['FIN'],
            'module'        => 'erp',
            'status'        => 1,
        ]);

        UserModuleAccess::updateOrCreate(
            ['user_id' => $multi->id, 'module' => 'soho'],
            ['reason' => '验收多模块分流', 'expires_at' => null]
        );
    }
}
