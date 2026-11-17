<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\ExtensionApplication;
use App\Erp\Models\Project\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 待办（对应参考目录 todo*）
 * 聚合：我负责的任务、待我审核的任务、待我审批的延期、系统指派待办
 */
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $myTasks = ProjectTask::with(['project:id,name'])
            ->where('executor_id', $user->id)
            ->whereIn('status', ['pending', 'in_progress', 'rejected', 'overdue'])
            ->orderBy('end_date')
            ->limit(50)->get();

        $auditTasks = ProjectTask::with(['project:id,name', 'executor:id,name'])
            ->where('auditor_id', $user->id)
            ->whereIn('status', ['for_audit', 'overdue'])
            ->whereHas('files', fn ($q) => $q->where('status', 'pending'))
            ->orderBy('end_date')
            ->limit(50)->get();

        $extensions = ExtensionApplication::with(['task:id,name', 'project:id,name', 'applicant:id,name'])
            ->where('status', 'applying')
            ->where(function ($q) use ($user) {
                $q->where('applicant_id', '!=', $user->id); // 不给自己审批自己的申请
            })
            ->whereHas('project', fn ($q) => $q->visibleTo($user))
            ->orderBy('id')
            ->limit(50)->get();

        $customs = DB::table('erp_project_todos')
            ->where('user_id', $user->id)->whereNull('done_at')
            ->orderBy('due_date')->limit(50)->get();

        return view('app.todos.index', compact('myTasks', 'auditTasks', 'extensions', 'customs'));
    }

    public function complete(Request $request, int $todo)
    {
        DB::table('erp_project_todos')->where('id', $todo)
            ->where('user_id', $request->user()->id)
            ->update(['done_at' => now(), 'updated_at' => now()]);

        return back()->with('success', '待办已完成');
    }

    public function approveExtension(Request $request, int $application)
    {
        return app(ExtensionController::class)->approve($request, ExtensionApplication::findOrFail($application));
    }

    public function rejectExtension(Request $request, int $application)
    {
        return app(ExtensionController::class)->reject($request, ExtensionApplication::findOrFail($application));
    }
}
