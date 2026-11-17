<?php

namespace App\Pm\Http\Controllers;

use App\Pm\Models\Project;
use App\Pm\Models\Task;
use App\Pm\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(DashboardService $dashboardService)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $data = $dashboardService->getAdminDashboard();
            return view('pm::dashboard.admin', $data);
        } elseif ($user->isProjectManager()) {
            return $this->projectManagerDashboard(date('Y'));
        } else {
            $data = $dashboardService->getUserDashboard($user);
            return view('pm::dashboard.index', $data);
        }
    }

    /**
     * 项目总经理仪表盘（查看所有项目数据）
     */
    private function projectManagerDashboard($currentYear)
    {
        $allProjectIds = Project::pluck('id');

        $projectStats = (object) [
            'total' => $allProjectIds->count(),
            'in_progress' => Project::whereIn('status', [
                \App\Pm\States\Project\InProgress::class,
                \App\Pm\States\Project\Overdue::class,
            ])->count(),
            'completed_on_time' => Project::where('status', \App\Pm\States\Project\Completed::class)->count(),
            'completed_delayed' => Project::where('status', \App\Pm\States\Project\OverdueCompleted::class)->count(),
            'paused' => Project::where('status', \App\Pm\States\Project\Suspended::class)->count(),
        ];

        $monthlyProjectData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyProjectData[$i] = Project::whereMonth('created_at', $i)
                ->whereYear('created_at', $currentYear)
                ->count();
        }

        $projectStatusData = $this->getGlobalProjectStatusData();

        $totalTasks = Task::whereHas('project', fn($q) => $q->where('status', '!=', \App\Pm\States\Project\Suspended::class))->count();
        $completedTaskStates = [
            \App\Pm\States\Task\Completed::class,
            \App\Pm\States\Task\OverdueCompleted::class,
        ];
        $completedTasks = Task::whereIn('status', $completedTaskStates)
            ->whereHas('project', fn($q) => $q->where('status', '!=', \App\Pm\States\Project\Suspended::class))
            ->count();
        $overdueTasks = Task::where('end_date', '<', now())
            ->whereNotIn('status', $completedTaskStates)
            ->whereHas('project', fn($q) => $q->where('status', '!=', \App\Pm\States\Project\Suspended::class))
            ->count();

        $efficiencyStats = [
            'task_count' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => Task::whereIn('status', [
                \App\Pm\States\Task\Pending::class,
                \App\Pm\States\Task\Rejected::class,
            ])->whereHas('project', fn($q) => $q->where('status', '!=', \App\Pm\States\Project\Suspended::class))->count(),
            'overdue_tasks' => $overdueTasks,
            'task_completion_rate' => $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100)
                : 0,
            'total_files' => \App\Pm\Models\File::count(),
        ];

        return view('pm::dashboard.index', compact(
            'currentYear',
            'projectStats',
            'monthlyProjectData',
            'projectStatusData',
            'efficiencyStats'
        ));
    }

    /**
     * 全局项目状态分布（饼图数据）
     */
    private function getGlobalProjectStatusData(): array
    {
        return [
            '正常结案' => Project::where('status', \App\Pm\States\Project\Completed::class)->count(),
            '延期结案' => Project::where('status', \App\Pm\States\Project\OverdueCompleted::class)->count(),
            '进行中' => Project::whereIn('status', [
                \App\Pm\States\Project\InProgress::class,
                \App\Pm\States\Project\Overdue::class,
            ])->count(),
            '暂停' => Project::where('status', \App\Pm\States\Project\Suspended::class)->count(),
        ];
    }
}
