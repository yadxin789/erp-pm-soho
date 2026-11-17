<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\ExtensionApplication;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ProjectLog;
use App\Erp\Models\Project\ProjectTask;
use App\Erp\Models\Project\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * 项目任务：列表 / 我的任务 / 我的审核任务 / 详情 /
 * 上传交付文件 / 审核（通过·退回）/ 更新文件（版本递进）/ 下载
 *
 * 可见范围与项目一致：只看自己参与的项目下的任务。
 */
class TaskController extends Controller
{
    /** 状态分组（我的任务、我的审核任务的子页） */
    public const STATUS_GROUPS = [
        'pending' => ['pending'],
        'in_progress' => ['in_progress', 'rejected'],
        'for_audit' => ['for_audit'],
        'completed' => ['completed', 'overdue_completed'],
        'overdue' => ['overdue'],
    ];

    public const STATUS_GROUP_LABELS = [
        'pending' => '待执行',
        'in_progress' => '进行中',
        'for_audit' => '待审核',
        'completed' => '已完成',
        'overdue' => '已逾期',
    ];

    protected function visibleProjects()
    {
        $query = Project::query();

        if (! auth()->user()->hasRole('admin')) {
            $query->visibleTo(auth()->id());
        }

        return $query;
    }

    /**
     * 任务总列表（自己参与的项目下的全部任务）
     */
    public function index(Request $request)
    {
        $query = ProjectTask::query()
            ->whereIn('project_id', $this->visibleProjects()->pluck('id'))
            ->with(['project', 'executor', 'auditor', 'taskType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $tasks = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('app.tasks.index', [
            'tasks' => $tasks,
            'statusLabels' => ProjectTask::STATUS,
            'projects' => $this->visibleProjects()->orderBy('name')->get(['id', 'name']),
            'mode' => 'all',
        ]);
    }

    /** 我的任务（我是执行人） */
    public function myTasks(Request $request)
    {
        return $this->taskList($request, 'executor', 'my', 'all');
    }

    /** 我的任务 · 按状态子页 */
    public function myTasksStatus(Request $request, string $group)
    {
        abort_unless(isset(self::STATUS_GROUPS[$group]), 404);

        return $this->taskList($request, 'executor', 'my', $group);
    }

    /** 我的审核任务（我是审核人） */
    public function myAuditTasks(Request $request)
    {
        return $this->taskList($request, 'auditor', 'audit', 'all');
    }

    /** 我的审核任务 · 按状态子页 */
    public function myAuditTasksStatus(Request $request, string $group)
    {
        abort_unless(isset(self::STATUS_GROUPS[$group]), 404);

        return $this->taskList($request, 'auditor', 'audit', $group);
    }

    /**
     * 任务列表统一实现
     *
     * @param string $roleField executor / auditor
     * @param string $mode      my / audit（视图用）
     * @param string $group     all 或 STATUS_GROUPS 的键
     */
    protected function taskList(Request $request, string $roleField, string $mode, string $group)
    {
        $query = ProjectTask::query()
            ->where($roleField . '_id', auth()->id())
            ->with(['project', 'executor', 'auditor', 'taskType']);

        if ($group !== 'all') {
            $query->whereIn('status', self::STATUS_GROUPS[$group]);
        }

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 各分组计数
        $counts = ['all' => ProjectTask::where($roleField . '_id', auth()->id())->count()];
        foreach (self::STATUS_GROUPS as $key => $statuses) {
            $counts[$key] = ProjectTask::where($roleField . '_id', auth()->id())
                ->whereIn('status', $statuses)
                ->count();
        }

        $tasks = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('app.tasks.index', [
            'tasks' => $tasks,
            'statusLabels' => ProjectTask::STATUS,
            'projects' => $this->visibleProjects()->orderBy('name')->get(['id', 'name']),
            'mode' => $mode,
            'group' => $group,
            'groupLabels' => self::STATUS_GROUP_LABELS,
            'counts' => $counts,
            'roleField' => $roleField,
        ]);
    }

    public function show(ProjectTask $task)
    {
        $this->authorize('view', $task);

        $task->load(['project.manager', 'project.creator', 'executor', 'auditor', 'taskType', 'files.uploader', 'files.auditor']);
        $task->checkOverdue();

        return view('app.tasks.show', [
            'task' => $task,
            'files' => $task->files()->with(['uploader', 'auditor'])->get(),
            'canUpload' => $task->canUploadFile(),
            'canAudit' => $task->canAudit(),
            'canApplyExtension' => $task->canApplyExtension(),
            'canUpdateFile' => $task->canUpdateFile(),
            'canOperate' => $task->canOperateAsExecutor(),
            'statusLabels' => ProjectTask::STATUS,
            'priorityLabels' => ProjectTask::PRIORITY,
            'extensions' => \App\Erp\Models\Project\ExtensionApplication::with(['applicant:id,name', 'approver:id,name'])
                ->where('task_id', $task->id)
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    /** 执行人：开始任务（待执行/已驳回 → 进行中） */
    public function start(ProjectTask $task)
    {
        abort_unless($task->canOperateAsExecutor(), 403);
        abort_if(! in_array($task->status, ['pending', 'rejected'], true), 422, '当前状态不能开始');

        $task->status = 'in_progress';
        $task->save();

        $task->project?->calculateProgress();

        return back()->with('success', '任务已开始');
    }

    // ===================== 上传交付文件 =====================

    public function uploadForm(ProjectTask $task)
    {
        $this->authorize('uploadFile', $task);

        return view('app.tasks.upload', [
            'task' => $task,
            'mode' => 'upload',
        ]);
    }

    public function storeUpload(Request $request, ProjectTask $task)
    {
        $this->authorize('uploadFile', $task);

        $request->validate([
            'file' => 'required|file|max:20480',
            'remark' => 'nullable|string|max:500',
        ], [
            'file.required' => '请选择要上传的文件',
            'file.max' => '单个文件不能超过 20MB',
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('projects/' . $task->project_id . '/tasks/' . $task->id, 'local');

        $file = TaskFile::create([
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
            'version' => 1,
            'status' => 'pending',
            'uploaded_by' => auth()->id(),
        ]);

        $task->status = 'for_audit';
        $task->save();
        $task->project?->calculateProgress();

        ProjectLog::record($task->project_id, 'file_upload', '上传文件「' . $file->original_name . '」', ['task_id' => $task->id]);

        return redirect()->route('app.tasks.show', $task)->with('success', '文件已提交审核');
    }

    // ===================== 审核 =====================

    public function auditForm(ProjectTask $task)
    {
        $this->authorize('audit', $task);

        return view('app.tasks.audit', [
            'task' => $task,
            'file' => $task->pendingFile(),
        ]);
    }

    public function approve(Request $request, ProjectTask $task)
    {
        $this->authorize('audit', $task);

        $file = $task->pendingFile();
        abort_if(! $file, 422, '没有待审核的文件');

        $request->validate(['remark' => 'nullable|string|max:500']);

        $file->update([
            'status' => 'approved',
            'audited_by' => auth()->id(),
            'audited_at' => now(),
            'audit_remark' => $request->remark,
        ]);

        // 逾期状态下通过 → 逾期完成；否则 → 已完成
        $task->status = $task->is_overdue ? 'overdue_completed' : 'completed';
        $task->completed_at = now();
        $task->save();

        $task->project?->calculateProgress();

        ProjectLog::record($task->project_id, 'file_approve', '审核通过文件「' . $file->original_name . '」，任务「' . $task->name . '」完成', ['task_id' => $task->id]);

        return redirect()->route('app.tasks.show', $task)->with('success', '审核通过，任务已完成');
    }

    public function reject(Request $request, ProjectTask $task)
    {
        $this->authorize('audit', $task);

        $file = $task->pendingFile();
        abort_if(! $file, 422, '没有待审核的文件');

        $data = $request->validate([
            'remark' => 'required|string|max:500',
        ], ['remark.required' => '退回时必须填写原因']);

        $file->update([
            'status' => 'rejected',
            'audited_by' => auth()->id(),
            'audited_at' => now(),
            'audit_remark' => $data['remark'],
        ]);

        $task->status = 'rejected';
        $task->save();

        ProjectLog::record($task->project_id, 'file_reject', '退回文件「' . $file->original_name . '」：' . $data['remark'], ['task_id' => $task->id]);

        return redirect()->route('app.tasks.show', $task)->with('success', '已退回，执行人可重新上传');
    }

    // ===================== 更新文件（版本递进）=====================

    public function updateFileForm(ProjectTask $task)
    {
        $this->authorize('updateFile', $task);

        return view('app.tasks.upload', [
            'task' => $task,
            'mode' => 'update',
            'approvedFile' => $task->approvedFile(),
        ]);
    }

    public function storeUpdateFile(Request $request, ProjectTask $task)
    {
        $this->authorize('updateFile', $task);

        $request->validate([
            'file' => 'required|file|max:20480',
            'remark' => 'nullable|string|max:500',
        ], [
            'file.required' => '请选择要上传的新版本文件',
            'file.max' => '单个文件不能超过 20MB',
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('projects/' . $task->project_id . '/tasks/' . $task->id, 'local');

        $maxVersion = (int) TaskFile::where('task_id', $task->id)->max('version');
        $version = $maxVersion + 1;

        $file = TaskFile::create([
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
            'version' => $version,
            'status' => 'pending',
            'uploaded_by' => auth()->id(),
        ]);

        // 新版本重新进入审核，原已通过版本保留为历史
        $task->status = 'for_audit';
        $task->save();

        ProjectLog::record($task->project_id, 'file_update', '提交文件第 ' . $version . ' 版「' . $file->original_name . '」', ['task_id' => $task->id]);

        return redirect()->route('app.tasks.show', $task)->with('success', '新版本已提交审核');
    }

    // ===================== 下载 =====================

    /** 延期申请表单（执行人） */
    public function applyExtension(ProjectTask $task)
    {
        $this->authorize('view', $task);
        abort_unless($task->canApplyExtension(auth()->id()), 403, '只有任务执行人可以申请延期');

        return view('app.tasks.extension', ['task' => $task]);
    }

    /** 提交延期申请（由任务审核人审批） */
    public function storeExtension(Request $request, ProjectTask $task)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'required|string|max:500',
        ], [
            'days.required' => '请填写延期天数',
            'days.min' => '延期天数至少 1 天',
            'reason.required' => '请填写延期原因',
        ]);

        abort_unless($task->canApplyExtension(auth()->id()), 403, '只有任务执行人可以申请延期');

        $pending = ExtensionApplication::where('task_id', $task->id)->where('status', 'applying')->exists();
        if ($pending) {
            return back()->with('error', '该任务已有待审批的延期申请，请等待审批结果');
        }

        ExtensionApplication::create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'applicant_id' => auth()->id(),
            'days' => $validated['days'],
            'reason' => $validated['reason'],
            'status' => 'applying',
        ]);

        ProjectLog::record($task->project_id, 'extension_apply', "申请延期 {$validated['days']} 天：{$validated['reason']}", [
            'task_id' => $task->id,
        ]);

        return back()->with('success', '延期申请已提交，等待审核人审批');
    }

    public function download(ProjectTask $task, TaskFile $file)
    {
        $this->authorize('view', $task);

        abort_unless((int) $file->task_id === (int) $task->id, 404);
        abort_unless(Storage::disk('local')->exists($file->stored_path), 404, '文件不存在');

        return Storage::disk('local')->download($file->stored_path, $file->original_name);
    }
}
