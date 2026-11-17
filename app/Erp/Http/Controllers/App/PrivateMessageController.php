<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\PrivateMessage;
use App\Erp\Models\Project\Project;
use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * 项目组内私聊（对应参考目录 project/{project}/chat/{targetUserId} 与 api/project/{project}/chat/*）
 */
class PrivateMessageController extends Controller
{
    public function show(Request $request, Project $project, User $user)
    {
        $target = $user;
        Gate::authorize('view', $project);
        $this->assertParticipant($project, $target);

        $members = $project->participants()->reject(fn ($m) => (int) $m->id === (int) $request->user()->id)->values();

        return view('app.messages.private', [
            'project' => $project,
            'target' => $target,
            'members' => $members,
        ]);
    }

    /** 私聊历史（JSON） */
    public function history(Request $request, Project $project, User $user)
    {
        $target = $user;
        Gate::authorize('view', $project);
        $this->assertParticipant($project, $target);

        $me = $request->user()->id;

        $rows = PrivateMessage::with(['sender:id,name'])
            ->where('project_id', $project->id)
            ->where(function ($q) use ($me, $target) {
                $q->where(function ($w) use ($me, $target) {
                    $w->where('sender_id', $me)->where('receiver_id', $target->id);
                })->orWhere(function ($w) use ($me, $target) {
                    $w->where('sender_id', $target->id)->where('receiver_id', $me);
                });
            })
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'mine' => (int) $m->sender_id === (int) $me,
                'sender' => $m->sender->name ?? '',
                'content' => $m->content,
                'file_name' => $m->file_name,
                'time' => optional($m->created_at)->format('m-d H:i'),
            ]);

        // 标记已读
        PrivateMessage::where('project_id', $project->id)
            ->where('sender_id', $target->id)->where('receiver_id', $me)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['messages' => $rows]);
    }

    /** 发送私聊（JSON） */
    public function send(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|max:5000',
        ]);

        return response()->json(['ok' => true, 'message' => PrivateMessage::create([
            'project_id' => $project->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ])->only(['id'])]);
    }

    /** 可私聊成员（JSON） */
    public function members(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $rows = $project->participants()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->roles->first()->name ?? '',
                'url' => route('app.projects.messages.private', [$project, $u->id]),
            ])
            ->reject(fn ($u) => (int) $u['id'] === (int) $request->user()->id)
            ->values();

        return response()->json(['members' => $rows]);
    }

    /** 清空我与某人的记录 */
    public function clearMine(Request $request, Project $project, User $user)
    {
        $target = $user;
        Gate::authorize('view', $project);
        $this->assertParticipant($project, $target);

        PrivateMessage::where('project_id', $project->id)
            ->where('sender_id', $request->user()->id)
            ->where('receiver_id', $target->id)
            ->delete();

        return back()->with('success', '已清空我发出的私聊记录');
    }

    private function assertParticipant(Project $project, User $user): void
    {
        abort_unless(
            $project->participants()->contains(fn ($m) => (int) $m->id === (int) $user->id),
            403,
            '该用户不是本项目参与人'
        );
    }
}
