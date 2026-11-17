<?php

namespace App\Pm\Http\Controllers;

use App\Core\Services\VoiceService;
use App\Pm\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 项目电话会议（对齐原 PM VoiceController 的入口语义）
 * 与原 PM 的差异：原实现走 Janus 媒体服务器，本版复用 Core 的 WebRTC 信令（voice_rooms/voice_signals），
 * 房间类型固定 project、房间号 = 项目 id；非项目成员不显示入口（页面本身也会跳回项目页并提示）。
 */
class ProjectVoiceController extends Controller
{
    public function __construct(private readonly VoiceService $voice)
    {
    }

    public function index(Request $request, Project $project)
    {
        $user = $request->user();
        if (! $project->isMember($user)) {
            return redirect()->route('pm.project.show', $project)
                ->with('warning', '你不是本项目成员，项目电话会议入口已隐藏。');
        }

        $room = $this->voice->ensureRoom('pm', 'project', $project->id, $user, $project->name.' 电话会议');

        return view('pm::project.voice', [
            'project' => $project,
            'room'    => $room,
            'members' => $this->voice->members($room),
            'history' => $this->voice->history('pm', $project->id),
        ]);
    }
}
