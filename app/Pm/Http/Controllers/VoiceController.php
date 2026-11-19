<?php

namespace App\Pm\Http\Controllers;

use App\Core\Services\VoiceService;
use App\Pm\Models\CollabDocument;
use App\Pm\Models\Project;
use App\Pm\Services\CollabOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 项目电话会议（去 Janus / Swoole 外部依赖版）
 *
 * 信令从原 PM 的 WebSocket（signal_ws → Swoole → Janus AudioBridge）改为
 * Core 的站内中转（voice_signals 表 + 2 秒轮询），浏览器 WebRTC mesh 直连音频，
 * 无需外部媒体服务器与信令服务，内网/断外网可用。
 *
 * 原 hookEnter/hookLeave（Swoole 回调）保留为兼容入口，有外部信令服务时仍可回写同一张表。
 */
class VoiceController extends Controller
{
    public function __construct(private readonly VoiceService $voice)
    {
    }

    /** 严格鉴权：只有项目 participants() 包含的用户才能入会 */
    private function assertProjectMember(Project $project): void
    {
        $memberIds = $project->participants()->pluck('id');
        abort_unless($memberIds->contains(auth()->id()), 404, '非本项目成员，不能加入电话会议');
    }

    /** 复用/创建本项目房间（命名统一 project-{id}，便于历史归集） */
    private function room(Project $project): \App\Core\Models\VoiceRoom
    {
        return $this->voice->ensureRoom('pm', 'project', (int) $project->id, auth()->user());
    }

    public function index(Request $request, Project $project)
    {
        $this->assertProjectMember($project);

        $room = $this->room($project);
        $isHost = (int) $project->creator_id === (int) auth()->id();

        // 会议资料：项目内可下载的协作文档（在线预览依赖 OnlyOffice，本版以降级为下载查看）
        $documents = CollabDocument::where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'extension'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'ext' => $d->extension,
                'download_url' => route('pm.collab.download', $d),
            ]);

        return view('pm::project.voice', [
            'project' => $project,
            'room' => $room,
            'isHost' => $isHost,
            'activeTab' => 'voice',
            'documents' => $documents,
            'members' => $this->voice->members($room),
            'history' => $this->voice->history('pm', (int) $project->id),
        ]);
    }

    /** 入会令牌（HMAC，站内信令用） */
    public function issueToken(Request $request, Project $project)
    {
        $this->assertProjectMember($project);
        $room = $this->room($project);
        $token = $this->voice->token($room, auth()->user());

        return response()->json(array_merge($token, [
            'room' => [
                'id' => $room->id,
                'project_id' => $project->id,
                'status' => $room->status,
            ],
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->displayName(),
            ],
            'is_host' => (int) $project->creator_id === (int) auth()->id(),
            'signal_url' => route('pm.project.voice.signal', $project),
            'poll_url' => route('pm.project.voice.poll', $project),
            'end_url' => route('pm.project.voice.end', $project),
            'stun' => config('voice.stun'),
        ]));
    }

    /** 发信令：offer / answer / candidate / leave / heartbeat */
    public function signal(Request $request, Project $project)
    {
        $this->assertProjectMember($project);
        $data = $request->validate([
            'to' => 'nullable|integer',
            'type' => 'required|in:offer,answer,candidate,leave,heartbeat',
            'payload' => 'nullable',
        ]);

        try {
            $this->voice->signal($this->room($project), auth()->user(), $data['type'], $data['payload'] ?? null, $data['to'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    /** 拉信令 + 在会成员（每信令只投递一次） */
    public function poll(Request $request, Project $project)
    {
        $this->assertProjectMember($project);

        return response()->json($this->voice->poll($this->room($project), auth()->user()));
    }

    /** 结束会议（主持人） */
    public function end(Request $request, Project $project)
    {
        $this->assertProjectMember($project);
        $this->voice->close($this->room($project), auth()->user());

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', '会议已结束。');
    }

    /** 在会成员（前端下拉/侧栏刷新） */
    public function members(Project $project)
    {
        $this->assertProjectMember($project);

        return response()->json(['members' => $this->voice->members($this->room($project))]);
    }

    /** 会议历史（项目房间） */
    public function history(Project $project)
    {
        $this->assertProjectMember($project);

        return response()->json(['sessions' => $this->voice->history('pm', (int) $project->id)]);
    }

    /** 会议资料：只读 OnlyOffice 配置（保留兼容；前端已改为下载查看） */
    public function docConfig(Project $project, CollabDocument $doc, CollabOfficeService $office, Request $request)
    {
        $this->assertProjectMember($project);
        abort_unless($doc->project_id === $project->id, 404);

        try {
            $config = $office->buildEditorConfig($doc, auth()->user(), (bool) $request->query('edit', false));
        } catch (\Throwable $e) {
            return response()->json(['error' => '在线预览服务未配置：'.$e->getMessage()], 503);
        }

        return response()->json([
            'server_url' => $office->serverUrl(),
            'config' => $config,
            'download_url' => route('pm.collab.download', $doc),
        ]);
    }

    /** 上传本地演示/资料文件，创建临时 CollabDocument（7 天后自动清理） */
    public function uploadDemoFile(Project $project, Request $request)
    {
        $this->assertProjectMember($project);
        $allowed = ['docx', 'xlsx', 'pptx', 'pdf', 'doc', 'xls', 'ppt'];
        $data = $request->validate(['file' => 'required|file|max:26214400']);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (! in_array($ext, $allowed)) {
            return response()->json(['error' => '不支持的文件格式，仅支持 docx/xlsx/pptx/pdf'], 422);
        }

        $path = $request->file('file')->store('voice_demo', 'file_repository');
        $doc = CollabDocument::create([
            'project_id' => $project->id,
            'name' => pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME),
            'extension' => $ext,
            'storage_path' => $path,
            'doc_key' => \Illuminate\Support\Str::random(32),
            'size' => $request->file('file')->getSize(),
            'created_by' => auth()->id(),
            'is_temp_demo' => true,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        return response()->json(['id' => $doc->id, 'download_url' => route('pm.collab.download', $doc)]);
    }

    /** 兼容：外部信令服务回调入会 */
    public function hookEnter(Request $request)
    {
        $data = $request->validate(['room_id' => 'required|integer', 'user_id' => 'required|integer']);
        $room = \App\Core\Models\VoiceRoom::findOrFail($data['room_id']);
        $user = \App\Core\Models\User::findOrFail($data['user_id']);
        $this->voice->join($room, $user);

        return response()->json(['ok' => true, 'room_id' => $room->id]);
    }

    /** 兼容：外部信令服务回调离会 */
    public function hookLeave(Request $request)
    {
        $data = $request->validate(['room_id' => 'required|integer', 'user_id' => 'required|integer']);
        $room = \App\Core\Models\VoiceRoom::findOrFail($data['room_id']);
        $user = \App\Core\Models\User::findOrFail($data['user_id']);
        $this->voice->leave($room, $user);

        return response()->json(['ok' => true]);
    }
}
