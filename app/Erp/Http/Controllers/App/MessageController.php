<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ProjectMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 项目组群聊（对应参考目录 project/{project}/messages）
 */
class MessageController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $messages = ProjectMessage::with('user')
            ->where('project_id', $project->id)
            ->orderBy('id')
            ->limit(300)
            ->get();

        $members = $project->participants();

        return view('app.messages.index', compact('project', 'messages', 'members'));
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validate([
            'content' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:20480',
        ], [
            'content.max' => '单条消息不能超过 5000 字',
            'file.max' => '附件不能超过 20MB',
        ]);

        if (blank($data['content'] ?? null) && ! $request->hasFile('file')) {
            return back()->withErrors(['content' => '请输入消息内容或选择附件']);
        }

        $payload = [
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'content' => $data['content'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['file_name'] = $file->getClientOriginalName();
            $payload['file_path'] = $file->store("projects/{$project->id}/messages", 'local');
            $payload['file_size'] = $file->getSize();
            $payload['file_mime'] = $file->getMimeType();
        }

        ProjectMessage::create($payload);

        return redirect()->route('app.projects.messages.index', $project)->with('success', '消息已发送');
    }

    /** 清空我发出的消息（对应参考 messages/clear-mine） */
    public function clearMine(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        ProjectMessage::where('project_id', $project->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return redirect()->route('app.projects.messages.index', $project)->with('success', '已清空我发出的消息');
    }

    /** 删除单条消息：本人或项目创建人 */
    public function destroy(Request $request, Project $project, ProjectMessage $message)
    {
        Gate::authorize('view', $project);

        $isOwner = (int) $message->user_id === (int) $request->user()->id;
        $isCreator = (int) $project->creator_id === (int) $request->user()->id;

        abort_unless($isOwner || $isCreator, 403, '只能删除自己发出的消息');

        $message->delete();

        return redirect()->route('app.projects.messages.index', $project)->with('success', '消息已删除');
    }

    /** 附件下载（对应参考 messages/{id}/file） */
    public function file(ProjectMessage $message): StreamedResponse
    {
        Gate::authorize('view', $message->project);

        abort_unless($message->file_path && Storage::disk('local')->exists($message->file_path), 404, '文件不存在');

        return Storage::disk('local')->download($message->file_path, $message->file_name ?: basename($message->file_path));
    }
}
