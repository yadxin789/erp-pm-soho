<?php

namespace App\Pm\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Pm\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('tasks')->orderByDesc('created_at')->get();
        $projects->each(fn (Project $p) => $p->progress = $p->calculateProgress());

        $stats = [
            'total'       => $projects->count(),
            'in_progress' => $projects->where('status', 'in_progress')->count(),
            'completed'   => $projects->where('status', 'completed')->count(),
            'overdue'     => $projects->whereIn('status', ['overdue', 'overdue_completed'])->count(),
            'suspended'   => $projects->where('status', 'suspended')->count(),
        ];

        return view('pm::project.index', compact('projects', 'stats'));
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();
        $tasks = $project->tasks()->with('executor', 'auditor', 'department', 'taskType')->get();
        $canViewAll = $user->hasAnyRole(['core.super_admin', 'pm.project_manager']) || $user->id === $project->creator_id;
        $visibleTasks = $canViewAll
            ? $tasks
            : $tasks->filter(fn ($t) => $t->executor_id === $user->id || $t->auditor_id === $user->id)->values();

        $participants = $project->participants();
        $participatingDepts = $project->participatingDepartments();
        $stats = $project->taskStats();

        return view('pm::project.show', compact(
            'project', 'tasks', 'visibleTasks', 'participants', 'participatingDepts', 'stats', 'canViewAll'
        ));
    }

    public function gantt(Request $request, Project $project)
    {
        $tasks = $project->tasks()->with('executor', 'department')->get();

        return view('pm::project.gantt', compact('project', 'tasks'));
    }

    public function logs(Request $request, Project $project)
    {
        $logs = $project->logs()->with('user')->paginate(20);

        return view('pm::project.logs', compact('project', 'logs'));
    }

    public function history(Request $request, Project $project)
    {
        $logs = $project->logs()->with('user')->get();
        $tasks = $project->tasks()->with('executor', 'auditor', 'department')->orderByDesc('completed_at')->get();

        return view('pm::project.history', compact('project', 'logs', 'tasks'));
    }
}
