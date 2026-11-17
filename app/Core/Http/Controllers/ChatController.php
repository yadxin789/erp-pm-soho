<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\ChatMessage;
use App\Core\Models\User;
use App\Core\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * 统一聊天入口（TC-10）：ERP 群聊/私聊 + PM 项目留言板 三源合一。
 *
 * 页面参数：`?module=erp|pm|soho|core`（默认取 session 的活动模块）、
 *          `?room=group|project|private`、`?to=对方用户id`（私聊）、`?project=项目id`。
 * 模块内的旧入口（`app.chat.index`、`messages.index`）迁入时直接改跳这里。
 */
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);
        $roomType = $this->resolveRoomType($request);
        $roomId = $this->resolveRoomId($request, $roomType);

        $messages = $this->chat->listFor($user, $module, $roomType, $roomId);

        return view('core::chat.index', [
            'module' => $module,
            'roomType' => $roomType,
            'roomId' => $roomId,
            'roomKey' => $this->chat->roomKey($roomType, $roomId),
            'messages' => $messages,
            'members' => $this->memberOptions($user, $module),
            'peer' => $roomType === ChatMessage::ROOM_PRIVATE
                ? User::find($roomId)
                : null,
            'allowedExt' => ChatService::ALLOWED_EXT,
            'maxFileKb' => ChatService::MAX_FILE_KB,
        ]);
    }

    /** 轮询新消息（`?after=<id>`） */
    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);
        $roomType = $this->resolveRoomType($request);
        $roomId = $this->resolveRoomId($request, $roomType);
        $after = (int) $request->query('after', 0);

        $rows = $this->chat->listFor($user, $module, $roomType, $roomId, $after);

        return response()->json([
            'success' => true,
            'messages' => $rows->map(fn (ChatMessage $m) => $m->toFrontend($user->id))->values(),
        ]);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);
        $roomType = $this->resolveRoomType($request);
        $roomId = $this->resolveRoomId($request, $roomType);

        $request->validate([
            'content' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|max:'.ChatService::MAX_FILE_KB,
        ]);

        $files = $request->file('files') ?? [];

        try {
            $message = $this->chat->send(
                $user,
                $module,
                $roomType,
                $roomId,
                $request->input('content'),
                is_array($files) ? $files : [$files],
                $request->integer('reply_to') ?: null,
            );
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message->fresh()->toFrontend($user->id)]);
        }

        return back()->with('success', '已发送');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ok = $this->chat->deleteOwn($request->user(), $id);

        return response()->json(['success' => $ok], $ok ? 200 : 403);
    }

    public function clearMine(Request $request)
    {
        $user = $request->user();
        $module = $this->resolveModule($request, $user);
        $roomType = $this->resolveRoomType($request);
        $roomId = $this->resolveRoomId($request, $roomType);

        $this->chat->clearMine($user, $module, $roomType, $roomId);

        return back()->with('success', '已清空我看到的记录（不影响他人）');
    }

    public function file(Request $request, int $id)
    {
        $file = $this->chat->download($request->user(), $id, $request->integer('index', 0));
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return Storage::disk('public')->response($file['path'], $file['name'], [
            'Content-Disposition' => $disposition.'; filename="'.$file['name'].'"',
        ]);
    }

    /** 模块：显式传参优先，其次 session 里的活动模块，最后回落 core */
    private function resolveModule(Request $request, User $user): string
    {
        $module = $request->query('module') ?? $request->input('module') ?? session('active_module') ?? 'core';
        $this->chat->ensureModule($module);
        abort_unless(in_array($module, $user->resolveModules(), true), 403, '当前账号无权进入该模块的聊天');

        return $module;
    }

    private function resolveRoomType(Request $request): string
    {
        $type = $request->query('room') ?? $request->input('room') ?? ChatMessage::ROOM_GROUP;

        return $this->chat->ensureRoomType($type);
    }

    private function resolveRoomId(Request $request, string $roomType): int
    {
        return match ($roomType) {
            ChatMessage::ROOM_PRIVATE => (int) ($request->query('to') ?? $request->input('to') ?? 0),
            ChatMessage::ROOM_PROJECT => (int) ($request->query('project') ?? $request->input('project') ?? 0),
            default => 0,
        };
    }

    /** 私聊选择器：同模块内其它活跃用户 */
    private function memberOptions(User $user, string $module): array
    {
        return User::query()
            ->where('id', '!=', $user->id)
            ->where('status', 1)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'real_name', 'username', 'email'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->displayName()])
            ->values()
            ->all();
    }
}
