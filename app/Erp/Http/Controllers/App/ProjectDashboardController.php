<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\ExtensionApplication;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ProjectLog;
use App\Erp\Models\Project\ProjectTask;
use Illuminate\Http\Request;

/**
 * 产品研发项目仪表盘（仅统计本人参与的项目，对应参考目录 dashboard）
 */
class ProjectDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::visibleTo($user)->get();
        $ids = $projects->pluck('id');

        $tasks = ProjectTask::whereIn('project_id', $ids)->get();

        $cards = [
            'projects_total' => $projects->count(),
            'in_progress' => $projects->where('status', 'in_progress')->count(),
            'suspend' => $projects->where('status', 'suspended')->count(),
            'completed' => $projects->where('status', 'completed')->count(),
            'pending_start' => $projects->where('status', 'not_started')->count(),
        ];

        $taskStats = [
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'for_audit' => $tasks->where('status', 'for_audit')->count(),
            'completed' => $tasks->filter(fn ($t) => in_array($t->status, ['completed', 'overdue_completed'], true))->count(),
            'overdue' => $tasks->filter(fn ($t) => in_array($t->status, ['overdue', 'overdue_completed'], true) || (bool) $t->is_overdue)->count(),
            'rejected' => $tasks->where('status', 'rejected')->count(),
        ];

        $mine = [
            'executor' => $tasks->where('executor_id', $user->id)->count(),
            'auditor' => $tasks->where('auditor_id', $user->id)->count(),
        ];

        $pendingExtensions = ExtensionApplication::where('status', 'applying')
            ->whereIn('project_id', $ids)
            ->where('applicant_id', '!=', $user->id)
            ->count();

        $recentLogs = ProjectLog::with(['user:id,name', 'project:id,name'])
            ->whereIn('project_id', $ids)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $memberLoad = $tasks->groupBy('executor_id')->map(fn ($rows, $uid) => [
            'name' => optional($rows->first()->executor)->name ?? ('用户 #' . $uid),
            'total' => $rows->count(),
            'done' => $rows->filter(fn ($t) => in_array($t->status, ['completed', 'overdue_completed'], true))->count(),
        ])->sortByDesc('total')->take(8);

        return view('app.pm-dashboard.index', compact('projects', 'cards', 'taskStats', 'mine', 'pendingExtensions', 'recentLogs', 'memberLoad'));
    }
}
