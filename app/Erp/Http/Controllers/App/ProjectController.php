<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\MasterData\Customer;
use App\Core\Models\Department;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ProjectLog;
use App\Erp\Models\Project\ProjectTask;
use App\Erp\Models\Project\TimeEntry;
use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 产品研发项目：项目主体（列表 / 我的项目 / 详情 / 甘特 / 履历 / 变更 / 暂停恢复）
 *
 * 可见范围：每个人只能看到「自己参与的项目」（创建人 / 负责人 / 任一任务执行人或审核人）；
 * 系统管理员可看全部。
 */
class ProjectController extends Controller
{
    /**
     * 按可见范围取项目查询
     */
    protected function visibleQuery()
    {
        $query = Project::query();

        if (! auth()->user()->hasRole('admin')) {
            $query->visibleTo(auth()->id());
        }

        return $query;
    }

    /**
     * 可见范围内的状态计数
     */
    protected function statusCounts()
    {
        $rows = $this->visibleQuery()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts = ['all' => array_sum($rows)];
        foreach (array_keys(Project::STATUS) as $status) {
            $counts[$status] = (int) ($rows[$status] ?? 0);
        }

        return $counts;
    }

    public function index(Request $request)
    {
        $query = $this->visibleQuery()->with(['manager', 'creator'])->withCount('tasks');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('code', 'like', '%' . $request->keyword . '%')
                    ->orWhere('product_name', 'like', '%' . $request->keyword . '%');
            });
        }

        $projects = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('app.projects.index', [
            'projects' => $projects,
            'counts' => $this->statusCounts(),
            'activeStatus' => $request->get('status', 'all'),
            'statusLabels' => Project::STATUS,
            'isMine' => false,
        ]);
    }

    /**
     * 我的项目（我参与的全部项目）
     */
    public function myProjects(Request $request)
    {
        $query = Project::query();

        if (! auth()->user()->hasRole('admin')) {
            $query->visibleTo(auth()->id());
        }

        $query->with(['manager'])->withCount('tasks');

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $projects = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('app.projects.index', [
            'projects' => $projects,
            'counts' => $this->statusCounts(),
            'activeStatus' => 'all',
            'statusLabels' => Project::STATUS,
            'isMine' => true,
        ]);
    }

    /**
     * 我的项目 · 按状态（参考目录的 4 个子页）
     */
    public function myProjectsStatus(Request $request, string $status)
    {
        abort_unless(array_key_exists($status, Project::STATUS), 404);

        $query = Project::query();

        if (! auth()->user()->hasRole('admin')) {
            $query->visibleTo(auth()->id());
        }

        $projects = $query->where('status', $status)
            ->with(['manager'])
            ->withCount('tasks')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        return view('app.projects.index', [
            'projects' => $projects,
            'counts' => $this->statusCounts(),
            'activeStatus' => $status,
            'statusLabels' => Project::STATUS,
            'isMine' => true,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        return view('app.projects.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
            'taskTypes' => \App\Erp\Models\Project\TaskType::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'product_name' => 'nullable|string|max:200',
            'product_model' => 'nullable|string|max:200',
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(Project::STATUS)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'manager_id' => 'nullable|exists:users,id',

            'tasks' => 'required|array|min:1',
            'tasks.*.name' => 'required_with:tasks|string|max:200',
            'tasks.*.task_type_id' => 'nullable|exists:task_types,id',
            'tasks.*.executor_id' => 'required_with:tasks|exists:users,id',
            'tasks.*.auditor_id' => 'required_with:tasks|exists:users,id',
            'tasks.*.start_date' => 'nullable|date',
            'tasks.*.end_date' => 'nullable|date',
            'tasks.*.priority' => 'nullable|in:1,2,3',
        ], [
            'name.required' => '请填写项目名称',
            'end_date.after_or_equal' => '结束日期不能早于开始日期',
            'tasks.required' => '请至少添加一个项目任务，并为任务指定执行人与审核人',
            'tasks.*.executor_id.required_with' => '每个任务都必须指定任务执行人',
            'tasks.*.auditor_id.required_with' => '每个任务都必须指定任务审核人',
        ]);

        // 执行人与审核人不能是同一个人（参考目录不允许自审）
        foreach (($data['tasks'] ?? []) as $index => $task) {
            if (! empty($task['executor_id']) && (int) $task['executor_id'] === (int) ($task['auditor_id'] ?? 0)) {
                return back()->withInput()->withErrors([
                    "tasks.{$index}.auditor_id" => '任务审核人不能与任务执行人相同（不允许自审）',
                ]);
            }
        }

        $project = DB::transaction(function () use ($data) {
            $seq = ((int) Project::withTrashed()->max('id')) + 1;

            $project = Project::create([
                'name' => $data['name'],
                'code' => 'PRJ' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                'product_name' => $data['product_name'] ?? null,
                'product_model' => $data['product_model'] ?? null,
                'version' => $data['version'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'progress' => 0,
                'creator_id' => auth()->id(),
                'manager_id' => $data['manager_id'] ?? auth()->id(),
                'customer_id' => $data['customer_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'budget' => $data['budget'] ?? 0,
            ]);

            foreach (($data['tasks'] ?? []) as $task) {
                ProjectTask::create([
                    'project_id' => $project->id,
                    'task_type_id' => $task['task_type_id'] ?? null,
                    'name' => $task['name'],
                    'description' => $task['description'] ?? null,
                    'executor_id' => $task['executor_id'],
                    'auditor_id' => $task['auditor_id'],
                    'assignee_id' => $task['executor_id'],
                    'status' => 'pending',
                    'priority' => $task['priority'] ?? 2,
                    'start_date' => $task['start_date'] ?? $project->start_date,
                    'end_date' => $task['end_date'] ?? $project->end_date,
                    'due_date' => $task['end_date'] ?? $project->end_date,
                ]);
            }

            ProjectLog::record($project->id, 'created', '创建项目「' . $project->name . '」');

            return $project;
        });

        return redirect()->route('app.projects.show', $project)->with('success', '项目已创建');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['manager', 'creator', 'customer']);
        $project->load(['tasks' => fn ($q) => $q->with(['executor', 'auditor', 'taskType', 'files'])->orderBy('start_date')]);

        $timeEntries = TimeEntry::with(['user', 'task'])
            ->whereIn('project_task_id', $project->tasks->pluck('id'))
            ->latest('date')
            ->limit(20)
            ->get();

        // 每个任务对当前用户的可用操作，供视图显示按钮
        $abilities = [];
        foreach ($project->tasks as $task) {
            $abilities[$task->id] = [
                'upload' => $task->canUploadFile(),
                'audit' => $task->canAudit(),
                'extension' => $task->canApplyExtension(),
                'update_file' => $task->canUpdateFile(),
                'operate' => $task->canOperateAsExecutor(),
            ];
        }

        return view('app.projects.show', [
            'project' => $project,
            'timeEntries' => $timeEntries,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
            'taskStatusLabels' => ProjectTask::STATUS,
            'priorityLabels' => ProjectTask::PRIORITY,
            'abilities' => $abilities,
            'logs' => $project->logs()->with('user')->limit(10)->get(),
        ]);
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('app.projects.edit', [
            'project' => $project,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'product_name' => 'nullable|string|max:200',
            'product_model' => 'nullable|string|max:200',
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(Project::STATUS)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'manager_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => '请填写项目名称',
            'end_date.after_or_equal' => '结束日期不能早于开始日期',
        ]);

        $project->update($data);

        ProjectLog::record($project->id, 'updated', '编辑项目「' . $project->name . '」');

        return redirect()->route('app.projects.show', $project)->with('success', '项目已更新');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->deleted_by = auth()->id();
        $project->saveQuietly();
        $project->delete();

        ProjectLog::record($project->id, 'updated', '删除项目「' . $project->name . '」');

        return redirect()->route('app.projects.index')->with('success', '项目已删除（可在管理员回收站恢复）');
    }

    /**
     * 项目履历
     */
    public function logs(Project $project)
    {
        $this->authorize('view', $project);

        return view('app.projects.logs', [
            'project' => $project,
            'logs' => $project->logs()->with('user')->paginate(30),
        ]);
    }

    /**
     * 项目变更（改排期 / 改负责人，留痕）
     */
    public function changeForm(Project $project)
    {
        $this->authorize('change', $project);

        return view('app.projects.change', [
            'project' => $project,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function changeUpdate(Request $request, Project $project)
    {
        $this->authorize('change', $project);

        $data = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'manager_id' => 'nullable|exists:users,id',
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => '请填写变更原因',
            'end_date.after_or_equal' => '结束日期不能早于开始日期',
        ]);

        $before = [
            'start_date' => optional($project->start_date)->toDateString(),
            'end_date' => optional($project->end_date)->toDateString(),
            'manager_id' => $project->manager_id,
        ];

        $project->update([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'manager_id' => $data['manager_id'] ?? $project->manager_id,
        ]);

        ProjectLog::record($project->id, 'change', '项目变更：' . $data['reason'], [
            'before' => $before,
            'after' => [
                'start_date' => optional($project->start_date)->toDateString(),
                'end_date' => optional($project->end_date)->toDateString(),
                'manager_id' => $project->manager_id,
            ],
        ]);

        return redirect()->route('app.projects.show', $project)->with('success', '项目变更已保存');
    }

    /**
     * 甘特图数据
     */
    public function gantt(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['tasks.executor', 'tasks.auditor']);

        return view('app.projects.gantt', [
            'project' => $project,
            'tasks' => $project->tasks,
        ]);
    }

    public function pause(Request $request, Project $project)
    {
        $this->authorize('pause', $project);

        $request->validate(['reason' => 'required|string|max:500'], ['reason.required' => '请填写暂停原因']);

        $project->update([
            'status' => 'suspended',
            'paused_reason' => $request->reason,
            'paused_at' => now(),
        ]);

        ProjectLog::record($project->id, 'pause', '暂停项目：' . $request->reason);

        return back()->with('success', '项目已暂停，任务操作暂时冻结');
    }

    public function resume(Project $project)
    {
        $this->authorize('resume', $project);

        $pausedDays = $project->paused_at ? (int) $project->paused_at->diffInDays(now()) : 0;

        $project->update([
            'status' => 'in_progress',
            'resumed_at' => now(),
            'total_paused_days' => (int) $project->total_paused_days + $pausedDays,
        ]);

        // 恢复后按任务重算，可能直接回到已完成
        $project->calculateProgress();

        ProjectLog::record($project->id, 'resume', '恢复项目，本次暂停 ' . $pausedDays . ' 天');

        return back()->with('success', '项目已恢复');
    }

    // ===================== 任务维护（项目内）=====================

    public function storeTask(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'task_type_id' => 'nullable|exists:task_types,id',
            'executor_id' => 'required|exists:users,id',
            'auditor_id' => 'required|exists:users,id',
            'priority' => 'nullable|in:1,2,3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.required' => '请填写任务名称',
            'executor_id.required' => '请指定任务执行人',
            'auditor_id.required' => '请指定任务审核人',
        ]);

        if ((int) $data['executor_id'] === (int) $data['auditor_id']) {
            return back()->withInput()->withErrors(['auditor_id' => '任务审核人不能与任务执行人相同']);
        }

        $data['project_id'] = $project->id;
        $data['assignee_id'] = $data['executor_id'];
        $data['status'] = 'pending';
        $data['priority'] = $data['priority'] ?? 2;
        $data['due_date'] = $data['end_date'] ?? null;

        $task = ProjectTask::create($data);

        ProjectLog::record($project->id, 'task_created', '新增任务「' . $task->name . '」', ['task_id' => $task->id]);

        return redirect()->route('app.projects.show', $project)->with('success', '任务已添加');
    }

    public function updateTask(Request $request, ProjectTask $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'name' => 'nullable|string|max:200',
            'status' => 'nullable|in:' . implode(',', array_keys(ProjectTask::STATUS)),
            'priority' => 'nullable|in:1,2,3',
            'executor_id' => 'nullable|exists:users,id',
            'auditor_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if (isset($data['executor_id'], $data['auditor_id'])
            && (int) $data['executor_id'] === (int) $data['auditor_id']) {
            return back()->withErrors(['auditor_id' => '任务审核人不能与任务执行人相同']);
        }

        $task->update($data);

        if (isset($data['end_date'])) {
            $task->due_date = $data['end_date'];
            $task->save();
        }

        $task->project?->calculateProgress();

        ProjectLog::record($task->project_id, 'task_updated', '编辑任务「' . $task->name . '」', ['task_id' => $task->id]);

        return back()->with('success', '任务已更新');
    }

    public function destroyTask(ProjectTask $task)
    {
        $this->authorize('delete', $task);

        $projectId = $task->project_id;
        $task->timeEntries()->delete();
        $task->deleted_by = auth()->id();
        $task->saveQuietly();
        $task->delete();

        return redirect()->route('app.projects.show', $projectId)->with('success', '任务已删除');
    }

    // ===================== 工时 =====================

    public function storeTime(Request $request, ProjectTask $task)
    {
        $this->authorize('view', $task);

        $data = $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string',
        ], [
            'date.required' => '请选择日期',
            'hours.required' => '请填写工时',
            'hours.max' => '单日工时不能超过 24 小时',
        ]);

        $data['project_task_id'] = $task->id;
        $data['user_id'] = auth()->id();

        TimeEntry::create($data);

        return redirect()->route('app.projects.show', $task->project_id)->with('success', '工时已登记');
    }

    public function destroyTime(TimeEntry $entry)
    {
        $projectId = $entry->task?->project_id;

        abort_if($entry->user_id !== auth()->id() && ! auth()->user()->hasRole('admin'), 403);

        $entry->delete();

        return redirect()->route('app.projects.show', $projectId)->with('success', '工时记录已删除');
    }
}
