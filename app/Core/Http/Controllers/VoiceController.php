<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\User;
use App\Core\Models\VoiceRoom;
use App\Core\Services\VoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

/**
 * 统一语音会议入口（TC-10，母版取 ERP）。
 *
 * 参数：`?module=`、`?type=project|group|adhoc`、`?room=<room_key>`（project 时是项目 id）。
 * 对外 JSON：
 *   POST /voice/token        签发入会令牌（HMAC）
 *   POST /voice/signal       发 offer/answer/candidate/leave/heartbeat
 *   GET  /voice/poll         拉信令 + 在会成员（每信令只投递一次）
 *   GET  /voice/members      在会成员
 *   POST /voice/end          结束会议
 *   GET  /voice/history      会议历史
 * 兼容入口（PM 有外部 Swoole 信令服务时回写同一张表）：
 *   POST /voice/hook/enter、POST /voice/hook/leave
 */
class VoiceController extends Controller
{
    public function __construct(private readonly VoiceService $voice)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);
        $type = $request->query('room_type', 'group');
        $roomKey = (int) $request->query('room', 0);

        $room = $this->voice->ensureRoom($module, $type, $roomKey, $user);

        return view('core::voice.index', [
            'module' => $module,
            'type' => $type,
            'roomKey' => $roomKey,
            'room' => $room,
            'members' => $this->voice->members($room),
            'history' => $this->voice->history($module, $type === 'project' ? $roomKey : 0),
        ]);
    }

    public function token(Request $request): JsonResponse
    {
        $user = $request->user();
        [$room] = $this->context($request, $user);

        return response()->json($this->voice->token($room, $user));
    }

    public function signal(Request $request): JsonResponse
    {
        $user = $request->user();
        [$room] = $this->context($request, $user);

        $data = $request->validate([
            'to' => 'nullable|integer',
            'type' => 'required|in:offer,answer,candidate,leave,heartbeat',
            'payload' => 'nullable',
        ]);

        try {
            $this->voice->signal($room, $user, $data['type'], $data['payload'] ?? null, $data['to'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        [$room] = $this->context($request, $user);

        return response()->json($this->voice->poll($room, $user));
    }

    public function members(Request $request): JsonResponse
    {
        $user = $request->user();
        [$room] = $this->context($request, $user);

        return response()->json(['members' => $this->voice->members($room)]);
    }

    public function end(Request $request)
    {
        $user = $request->user();
        [$room] = $this->context($request, $user);

        $this->voice->close($room, $user);

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', '会议已结束');
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);

        return response()->json(['rooms' => $this->voice->history($module)]);
    }

    /** 兼容 PM 外部信令服务的回调：入会 */
    public function hookEnter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $room = VoiceRoom::findOrFail($data['room_id']);
        $user = User::findOrFail($data['user_id']);
        $this->voice->join($room, $user);

        return response()->json(['ok' => true, 'room_id' => $room->id]);
    }

    /** 兼容 PM 外部信令服务的回调：离会 */
    public function hookLeave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $room = VoiceRoom::findOrFail($data['room_id']);
        $user = User::findOrFail($data['user_id']);
        $this->voice->leave($room, $user);

        return response()->json(['ok' => true]);
    }

    /** 统一解析上下文并复用房间 */
    private function context(Request $request, User $user): array
    {
        $module = $this->resolveModule($request, $user);
        $type = $request->input('room_type', $request->query('room_type', 'group'));
        $roomKey = (int) $request->input('room', $request->query('room', 0));
        $room = $this->voice->ensureRoom($module, $type, $roomKey, $user);

        return [$room, $module, $type, $roomKey];
    }

    private function resolveModule(Request $request, User $user): string
    {
        $module = $request->query('module') ?? $request->input('module') ?? session('active_module') ?? 'core';
        $this->voice->ensureModule($module);
        abort_unless(in_array($module, $user->resolveModules(), true), 403, '当前账号无权进入该模块的语音会议');

        return $module;
    }
}
