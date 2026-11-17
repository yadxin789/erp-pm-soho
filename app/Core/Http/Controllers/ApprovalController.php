<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\ApprovalFlow;
use App\Core\Models\ApprovalInstance;
use App\Core\Models\ApprovalTask;
use App\Core\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

/**
 * 审批中心（TC-11）：待我审批 / 我发起的 / 实例详情（同意、驳回、撤销）。
 *
 * 当前账号能进哪些模块由准入链（D15）决定，页面按 active_module 收窄：
 * 待办里只展示当前模块的，避免 ERP 账号看到 SOHO 的报销单。
 */
class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $module = $request->session()->get('active_module', 'core');
        $tab = $request->string('tab')->toString() === 'mine' ? 'mine' : 'todo';

        $todo = $this->service->pendingFor($user, $module);
        $mine = $this->service->submittedBy($user, $module);

        $finished = ApprovalInstance::query()
            ->forModule($module)
            ->where('status', '!=', ApprovalInstance::STATUS_PENDING)
            ->with(['flow', 'applicant'])
            ->orderByDesc('finished_at')
            ->limit(10)
            ->get();

        return view('core::approvals.index', compact('module', 'tab', 'todo', 'mine', 'finished'));
    }

    public function show(Request $request, ApprovalInstance $approval)
    {
        $this->authorizeInstance($request, $approval);

        $approval->load(['flow.steps', 'applicant', 'tasks.approver']);

        // 同一人可能是多步的审批人（如 admin 既是采购经理又是财务），必须取「待处理」那一条
        $myTask = $approval->tasks->first(fn ($task) => $task->approver_id === $request->user()->id
            && $task->status === ApprovalTask::STATUS_PENDING);

        $canCancel = $approval->isPending()
            && ($approval->applicant_id === $request->user()->id || $request->user()->is_admin);

        return view('core::approvals.show', [
            'instance' => $approval,
            'myTask' => $myTask,
            'canCancel' => $canCancel,
        ]);
    }

    public function create(Request $request)
    {
        $module = $request->session()->get('active_module', 'core');

        return view('core::approvals.create', [
            'module' => $module,
            'flows' => ApprovalFlow::query()->forModule($module)->enabled()->with('steps')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $module = $request->session()->get('active_module', 'core');

        $data = $request->validate([
            'approval_flow_id' => ['required', 'integer'],
            'document_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $flow = ApprovalFlow::query()->forModule($module)->enabled()->with('steps')->find($data['approval_flow_id']);
        abort_unless($flow, 404, '该审批流不属于当前模块或已停用');

        try {
            $instance = $this->service->start(
                $flow,
                $request->user(),
                $flow->document_type,
                (int) $data['document_id'],
                $data['title'],
                ['note' => $data['note'] ?? null]
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('core.approvals.show', $instance)
            ->with('success', '已提交审批：'.$instance->title);
    }

    public function approve(Request $request, ApprovalInstance $approval, ApprovalTask $task)
    {
        return $this->act($request, $approval, $task, 'approve');
    }

    public function reject(Request $request, ApprovalInstance $approval, ApprovalTask $task)
    {
        return $this->act($request, $approval, $task, 'reject');
    }

    public function cancel(Request $request, ApprovalInstance $approval)
    {
        $this->authorizeInstance($request, $approval);

        try {
            $this->service->cancel($approval, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', '已撤销该审批');
    }

    private function act(Request $request, ApprovalInstance $approval, ApprovalTask $task, string $action)
    {
        $this->authorizeInstance($request, $approval);
        abort_unless($task->approval_instance_id === $approval->id, 404);

        $data = $request->validate(['comment' => ['nullable', 'string', 'max:500']]);

        try {
            if ($action === 'approve') {
                $this->service->approve($task, $request->user(), $data['comment'] ?? null);
                $msg = '已同意';
            } else {
                $this->service->reject($task, $request->user(), $data['comment'] ?? null);
                $msg = '已驳回';
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('core.approvals.show', $approval)->with('success', $msg.'：'.$approval->title);
    }

    /**
     * 只允许「本模块的审批 + 与自己相关（我是发起人/我是某一步的审批人/管理员）」查看。
     */
    private function authorizeInstance(Request $request, ApprovalInstance $instance): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        abort_if($instance->module !== $request->session()->get('active_module', 'core')
            && ! $user->is_admin, 403, '跨模块审批不可见');

        $related = $instance->applicant_id === $user->id
            || $instance->tasks()->where('approver_id', $user->id)->exists()
            || $user->is_admin;

        abort_unless($related, 403, '这条审批与你无关');
    }
}
