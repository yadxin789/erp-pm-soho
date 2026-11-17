<?php

namespace App\Pm\Http\Controllers;

use App\Pm\Models\Project;
use App\Pm\Models\ProjectMessage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * 项目内聊天（对齐原 PM MessageController / PrivateMessageController）
 * - 项目群聊：项目内所有成员可见
 * - 项目内私聊：project/{project}/chat/{targetUserId}
 * - 「清空我的留言板」只删自己的可见记录（原 PM clear-mine 语义）
 */
class ProjectMessageController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $user = $request->user();
        $peerId = (int) $request->query('peer', 0);

        $messages = $project->messages()
            ->with('sender')
            ->where('is_private', false)
            ->orderBy('created_at')
            ->get()
            ->reject(fn ($m) => $m->user_id === $user->id && $m->deleted_at !== null);

        return view('pm::project.messages', [
            'project'      => $project,
            'messages'     => $messages,
            'participants' => $project->participants(),
            'peer'         => null,
            'peerId'       => $peerId,
        ]);
    }

    public function privateChat(Request $request, Project $project, int $targetUserId)
    {
        $user = $request->user();
        $peer = $project->participants()->firstWhere('id', $targetUserId);
        abort_if($peer === null, 404);

        $messages = $project->messages()
            ->with('sender')
            ->where('is_private', true)
            ->where(function ($q) use ($user, $targetUserId) {
                $q->where(fn ($q2) => $q2->where('user_id', $user->id)->where('receiver_id', $targetUserId))
                  ->orWhere(fn ($q2) => $q2->where('user_id', $targetUserId)->where('receiver_id', $user->id));
            })
            ->orderBy('created_at')
            ->get();

        return view('pm::project.messages', [
            'project'      => $project,
            'messages'     => $messages,
            'participants' => $project->participants(),
            'peer'         => $peer,
            'peerId'       => $targetUserId,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'content'  => ['nullable', 'string', 'max:2000'],
            'peer'     => ['nullable', 'integer'],
            'file'     => ['nullable', 'file', 'max:20480'],
        ]);

        $user = $request->user();
        $content = trim((string) ($data['content'] ?? ''));
        $file = $request->file('file');

        abort_if($content === '' && $file === null, 422, '消息不能为空');

        $path = null;
        $name = null;
        if ($file !== null) {
            $path = $file->store('pm/project/'.$project->id, 'local');
            $name = $file->getClientOriginalName();
        }

        ProjectMessage::create([
            'project_id'  => $project->id,
            'user_id'     => $user->id,
            'receiver_id' => $data['peer'] ?? null,
            'is_private'  => ! empty($data['peer']),
            'content'     => $content,
            'file_path'   => $path,
            'file_name'   => $name,
        ]);

        return redirect()->back()->with('status', '已发送');
    }

    public function clearMine(Request $request, Project $project)
    {
        $user = $request->user();
        $peer = (int) $request->input('peer', 0);

        $query = $project->messages()->where('user_id', $user->id);
        if ($peer > 0) {
            $query->where('is_private', true)->where('receiver_id', $peer);
        } else {
            $query->where('is_private', false);
        }
        $query->delete();

        return redirect()->back()->with('status', '已清空我的留言板（其他人的留言不受影响）');
    }

    public function attachment(Request $request, Project $project, ProjectMessage $message)
    {
        abort_unless($message->project_id === $project->id, 404);
        abort_if($message->file_path === null, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($message->file_path), 404);

        return $disk->download($message->file_path, $message->file_name ?: basename($message->file_path));
    }
}
