<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\ExtensionApplication;
use App\Erp\Models\Project\ProjectLog;
use Illuminate\Http\Request;

/**
 * 延期审批台（对应参考目录 extension* / 项目经理可见）
 */
class ExtensionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $visible = \App\Erp\Models\Project\Project::visibleTo($user)->pluck('id');

        $pending = ExtensionApplication::with(['task:id,name,end_date', 'project:id,name', 'applicant:id,name'])
            ->where('status', 'applying')
            ->whereIn('project_id', $visible)
            ->where('applicant_id', '!=', $user->id)
            ->orderBy('id')
            ->get();

        $handled = ExtensionApplication::with(['task:id,name', 'project:id,name', 'applicant:id,name', 'approver:id,name'])
            ->where('status', '!=', 'applying')
            ->whereIn('project_id', $visible)
            ->orderByDesc('approved_at')
            ->limit(30)
            ->get();

        return view('app.extensions.index', compact('pending', 'handled'));
    }

    public function approve(Request $request, ExtensionApplication $application)
    {
        $this->assertCanHandle($request, $application);

        if ($application->status !== 'applying') {
            return back()->with('warning', '该申请已处理');
        }

        $task = $application->task;
        $oldEnd = $task->end_date;

        $task->forceFill([
            'end_date' => $oldEnd ? \Illuminate\Support\Carbon::parse($oldEnd)->addDays($application->days) : $oldEnd,
            'is_overdue' => false,
            'extension_days' => (int) $task->extension_days + (int) $application->days,
            'status' => in_array($task->status, ['overdue', 'in_progress'], true) ? 'in_progress' : $task->status,
        ])->save();

        $application->forceFill([
            'status' => 'approved',
            'approver_id' => $request->user()->id,
            'approved_at' => now(),
            'remark' => $request->input('remark'),
        ])->save();

        ProjectLog::record($task->project_id, 'extension_approve', "延期 {$application->days} 天已批准", ['task_id' => $task->id]);

        return back()->with('success', '延期申请已批准，任务结束日期顺延 ' . $application->days . ' 天');
    }

    public function reject(Request $request, ExtensionApplication $application)
    {
        $this->assertCanHandle($request, $application);

        $request->validate(['remark' => 'required|string|max:200'], ['remark.required' => '驳回必须填写原因']);

        $application->forceFill([
            'status' => 'rejected',
            'approver_id' => $request->user()->id,
            'approved_at' => now(),
            'remark' => $request->input('remark'),
        ])->save();

        ProjectLog::record($application->project_id, 'extension_reject', '延期申请被驳回：' . $request->input('remark'), ['task_id' => $application->task_id]);

        return back()->with('success', '延期申请已驳回');
    }

    private function assertCanHandle(Request $request, ExtensionApplication $application): void
    {
        $user = $request->user();

        abort_if((int) $application->applicant_id === (int) $user->id, 403, '不能审批自己提交的延期申请');

        $project = $application->project;
        abort_unless($project, 404);
        abort_unless($project->isParticipant($user) || $user->hasRole('admin'), 403, '无权审批该项目');
    }
}
