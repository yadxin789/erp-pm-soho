<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\CollabDocument;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\VoiceCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * 电话会议（对应参考目录 project/{project}/voice*）
 *
 * 音频：浏览器 WebRTC（mesh），信令走应用内置中转（voice_signals / voice_presence），
 * 不依赖任何外部服务，断网（本机/内网）即可开会。
 */
class VoiceController extends Controller
{
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $room = $this->roomOf($project);
        $call = VoiceCall::firstOrCreate(
            ['project_id' => $project->id, 'room' => $room, 'ended_at' => null],
            ['started_by' => auth()->id(), 'started_at' => now()]
        );

        $documents = CollabDocument::where('project_id', $project->id)->orderByDesc('updated_at')->get();
        $history = VoiceCall::where('project_id', $project->id)->orderByDesc('id')->limit(10)->get();

        return view('app.voice.index', compact('project', 'call', 'room', 'documents', 'history'));
    }

    /** 入会令牌（前端据此建立 WebRTC 连接） */
    public function issueToken(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $room = $this->roomOf($project);
        $user = $request->user();
        $ts = now()->timestamp;
        $signature = hash_hmac('sha256', "{$room}|{$user->id}|{$ts}", config('app.key'));

        return response()->json([
            'room' => $room,
            'userId' => $user->id,
            'userName' => $user->name,
            'ts' => $ts,
            'signature' => $signature,
            'signal_endpoint' => route('app.api.voice.signal', $project),
            'poll_endpoint' => route('app.api.voice.poll', $project),
            'iceServers' => [],
        ]);
    }

    /** 在会成员 + 可邀请成员 */
    public function members(Project $project)
    {
        Gate::authorize('view', $project);

        $room = $this->roomOf($project);
        $online = DB::table('voice_presence')
            ->where('room', $room)
            ->where('heartbeat_at', '>=', now()->subSeconds(30))
            ->pluck('user_id')
            ->all();

        $rows = $project->participants()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'online' => in_array((int) $u->id, array_map('intval', $online), true),
        ])->values();

        return response()->json(['members' => $rows, 'online' => count($online)]);
    }

    public function history(Project $project)
    {
        Gate::authorize('view', $project);

        $rows = VoiceCall::where('project_id', $project->id)
            ->orderByDesc('id')->limit(20)->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'started_at' => optional($c->started_at)->format('Y-m-d H:i'),
                'ended_at' => optional($c->ended_at)->format('Y-m-d H:i'),
                'duration' => $c->duration_label,
                'starter' => $c->starter->name ?? '',
                'participants' => $c->participants ?? [],
            ]);

        return response()->json(['calls' => $rows]);
    }

    /** 演示文件上传（会议中共享 PPT / PDF / 图片） */
    public function uploadDemo(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $request->validate(['file' => 'required|file|max:51200'], ['file.max' => '演示文件不能超过 50MB']);

        $file = $request->file('file');
        $path = $file->store("projects/{$project->id}/voice", 'local');

        $call = VoiceCall::where('project_id', $project->id)->whereNull('ended_at')->orderByDesc('id')->first();
        $call?->forceFill(['demo_file' => $path])->save();

        return response()->json([
            'ok' => true,
            'name' => $file->getClientOriginalName(),
            'url' => Storage::disk('local')->url($path),
            'preview' => route('app.files.preview', ['file' => 0]) . '?path=' . urlencode($path),
        ]);
    }

    /** 打开已上传的演示文件（内联输出，会议演示用） */
    public function demo(Project $project)
    {
        Gate::authorize('view', $project);

        $call = VoiceCall::where('project_id', $project->id)->whereNotNull('demo_file')->orderByDesc('id')->first();
        abort_unless($call && Storage::disk('local')->exists($call->demo_file), 404, '没有可用的演示文件');

        return Storage::disk('local')->response($call->demo_file);
    }

    /** 结束会议并落库 */
    public function end(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $room = $this->roomOf($project);
        $call = VoiceCall::where('project_id', $project->id)->where('room', $room)->whereNull('ended_at')->orderByDesc('id')->first();

        if ($call) {
            $online = DB::table('voice_presence')->where('room', $room)->pluck('user_id')->all();
            $names = $project->participants()
                ->filter(fn ($u) => in_array((int) $u->id, array_map('intval', $online), true))
                ->pluck('name')->all();

            $call->forceFill([
                'ended_at' => now(),
                'duration_seconds' => $call->started_at ? $call->started_at->diffInSeconds(now()) : 0,
                'participants' => $names ?: [$request->user()->name],
            ])->save();
        }

        DB::table('voice_presence')->where('room', $room)->delete();
        DB::table('voice_signals')->where('room', $room)->delete();

        return redirect()->route('app.projects.voice.index', $project)->with('success', '会议已结束');
    }

    /** 发送 WebRTC 信令（offer / answer / candidate / leave） */
    public function signal(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validate([
            'to' => 'nullable|integer',
            'type' => 'required|in:offer,answer,candidate,leave,heartbeat',
            'payload' => 'nullable',
        ]);

        $room = $this->roomOf($project);
        $me = $request->user()->id;

        DB::table('voice_presence')->updateOrInsert(
            ['room' => $room, 'user_id' => $me],
            ['joined_at' => DB::raw('COALESCE(joined_at, NOW())'), 'heartbeat_at' => now()]
        );

        if ($data['type'] !== 'heartbeat') {
            DB::table('voice_signals')->insert([
                'room' => $room,
                'from_user' => $me,
                'to_user' => $data['to'] ?? null,
                'type' => $data['type'],
                'payload' => json_encode($data['payload'] ?? null, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /** 拉取发给我的信令 + 在线成员 */
    public function poll(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $room = $this->roomOf($project);
        $me = $request->user()->id;

        DB::table('voice_presence')->updateOrInsert(
            ['room' => $room, 'user_id' => $me],
            ['joined_at' => DB::raw('COALESCE(joined_at, NOW())'), 'heartbeat_at' => now()]
        );

        $signals = DB::table('voice_signals')
            ->where('room', $room)
            ->where('from_user', '!=', $me)
            ->where(function ($q) use ($me) {
                $q->whereNull('to_user')->orWhere('to_user', $me);
            })
            ->whereNull('consumed_at')
            ->orderBy('id')
            ->limit(50)
            ->get();

        if ($signals->isNotEmpty()) {
            DB::table('voice_signals')->whereIn('id', $signals->pluck('id'))->update(['consumed_at' => now()]);
        }

        $online = DB::table('voice_presence')->where('room', $room)
            ->where('heartbeat_at', '>=', now()->subSeconds(30))
            ->pluck('user_id')->all();

        $memberNames = $project->participants()
            ->filter(fn ($u) => in_array((int) $u->id, array_map('intval', $online), true))
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->values();

        return response()->json([
            'signals' => $signals->map(fn ($s) => [
                'from' => (int) $s->from_user,
                'type' => $s->type,
                'payload' => json_decode($s->payload, true),
            ])->values(),
            'members' => $memberNames,
        ]);
    }

    private function roomOf(Project $project): string
    {
        return 'pm-' . $project->id;
    }
}
