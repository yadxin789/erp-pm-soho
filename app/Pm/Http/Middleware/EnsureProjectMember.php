<?php

namespace App\Pm\Http\Middleware;

use App\Pm\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * 项目访问闸门（D27）：项目只属 PM 模块，且只有与该项目相关的人能进入。
 *
 * - 参数不存在（列表页/新建页）时直接放行，由控制器按角色过滤。
 * - 项目不存在或当前用户与项目无关时返回 404（不使用 403 页面）。
 */
class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('project') ?? $request->route('projectId');

        if ($param === null && ($task = $request->route('task')) !== null) {
            $taskProjectId = $task instanceof \App\Pm\Models\Task
                ? $task->project_id
                : optional(\App\Pm\Models\Task::find($task))->project_id;
            $param = $taskProjectId;
        }

        if ($param === null) {
            return $next($request);
        }

        $project = $param instanceof Project ? $param : Project::find($param);

        if (! $project || Gate::denies('view', $project)) {
            if ($request->expectsJson() || $request->is('*/api/*')) {
                return response()->json(['message' => '项目不存在或无权访问'], 404);
            }

            return redirect()->to($this->homeUrl($request->user()))
                ->with('warning', '该项目仅对项目相关人员开放。');
        }

        return $next($request);
    }

    /** 项目闸门不通过时回落到用户所属模块工作台（不产生 403 页面） */
    private function homeUrl($user): string
    {
        $routeName = match ($user?->module) {
            'pm'   => 'pm.dashboard',
            'soho' => 'soho.dashboard',
            default => 'admin.dashboard',
        };

        try {
            return route($routeName);
        } catch (\Throwable $e) {
            return url('/');
        }
    }
}
