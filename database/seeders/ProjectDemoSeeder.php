<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Pm\Models\Project;
use App\Pm\Models\ProjectLog;
use App\Pm\Models\Task;
use App\Pm\Models\TaskType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * PM 项目演示数据（对照参考项目 GUI-01）
 * - 部门：项目管理部（已存在）+ 产品规划部 / 外观设计部 / 结构设计部 / 硬件设计部
 * - 成员：pm_manager_1 / product_manager_1 / product_designer_1 / appearance_manager_1 /
 *        appearance_designer_1 / structure_manager_1 / structure_designer_1（密码 123456，module=pm）
 * - 项目：GUI-01，编码 G20260928173306，状态进行中，37 个待处理任务（与参考概览统计一致）
 */
class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $pmDept = Department::firstOrCreate(['code' => 'PM'], [
            'name' => '项目管理部', 'module' => 'pm', 'parent_id' => null, 'status' => 1,
        ]);

        $depts = [
            'PM-PLAN'   => '产品规划部',
            'PM-APPEAR' => '外观设计部',
            'PM-STRUCT' => '结构设计部',
            'PM-HW'     => '硬件设计部',
        ];
        $deptMap = ['项目管理部' => $pmDept];
        foreach ($depts as $code => $name) {
            $deptMap[$name] = Department::firstOrCreate(['code' => $code], [
                'name' => $name, 'module' => 'pm', 'parent_id' => null, 'status' => 1,
            ]);
        }

        $members = [
            ['username' => 'pm_manager_1', 'name' => '项目管理部项目创建人1', 'dept' => '项目管理部'],
            ['username' => 'product_manager_1', 'name' => '产品规划部任务审核人1', 'dept' => '产品规划部'],
            ['username' => 'product_designer_1', 'name' => '产品规划部任务执行人1', 'dept' => '产品规划部'],
            ['username' => 'appearance_manager_1', 'name' => '外观设计部任务审核人1', 'dept' => '外观设计部'],
            ['username' => 'appearance_designer_1', 'name' => '外观设计部任务执行人1', 'dept' => '外观设计部'],
            ['username' => 'structure_manager_1', 'name' => '结构设计部任务审核人1', 'dept' => '结构设计部'],
            ['username' => 'structure_designer_1', 'name' => '结构设计部任务执行人1', 'dept' => '结构设计部'],
        ];

        $users = [];
        foreach ($members as $m) {
            $users[$m['username']] = User::firstOrCreate(
                ['username' => $m['username']],
                [
                    'name'       => $m['name'],
                    'email'      => $m['username'].'@pm.demo',
                    'password'   => Hash::make('123456'),
                    'module'     => 'pm',
                    'department_id' => $deptMap[$m['dept']]->id,
                    'status'     => 1,
                ]
            );
        }

        $types = [];
        foreach (['需求分析', '方案设计', '评审', '打样验证'] as $i => $name) {
            $types[$name] = TaskType::firstOrCreate(['name' => $name], ['sort_order' => $i + 1, 'is_required' => true]);
        }

        $creator = $users['pm_manager_1'];

        $project = Project::firstOrCreate(['code' => 'G20260928173306'], [
            'name'          => 'GUI-01',
            'version'       => 'V1.0',
            'product_name'  => 'GUI-01',
            'product_model' => 'GUI-01',
            'creator_id'    => $creator->id,
            'start_date'    => '2026-09-28',
            'end_date'      => '2026-11-06',
            'status'        => 'in_progress',
            'progress'      => 0,
        ]);

        if ($project->tasks()->count() > 0) {
            return;
        }

        $plan = [
            '项目管理部'   => ['pm_manager_1', 'pm_manager_1', 5, '项目管理'],
            '产品规划部'   => ['product_designer_1', 'product_manager_1', 12, '产品规划'],
            '外观设计部'   => ['appearance_designer_1', 'appearance_manager_1', 10, '外观设计'],
            '结构设计部'   => ['structure_designer_1', 'structure_manager_1', 10, '结构设计'],
        ];

        $n = 1;
        foreach ($plan as $deptName => [$execKey, $auditKey, $count, $prefix]) {
            $dept = $deptMap[$deptName];
            $exec = $users[$execKey];
            $audit = $users[$auditKey];
            for ($i = 1; $i <= $count; $i++, $n++) {
                Task::create([
                    'project_id'    => $project->id,
                    'department_id' => $dept->id,
                    'name'          => sprintf('%s-%02d %s阶段任务', $prefix, $i, $deptName),
                    'executor_id'   => $exec->id,
                    'auditor_id'    => $audit->id,
                    'start_date'    => '2026-09-28',
                    'end_date'      => '2026-11-06',
                    'status'        => 'pending',
                    'task_type_id'  => $types['需求分析']->id,
                ]);
            }
        }

        ProjectLog::create([
            'project_id' => $project->id,
            'user_id'    => $creator->id,
            'action'     => 'project.create',
            'content'    => '创建项目 GUI-01（G20260928173306），初始 37 个任务已按部门下发。',
        ]);
    }
}
