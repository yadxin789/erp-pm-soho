<?php

namespace App\Core\Services;

use App\Core\Models\ChatClearState;
use App\Core\Models\ChatMessage;
use App\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * 统一聊天服务（TC-10）。
 *
 * 三源合并：ERP 的群聊 / 私聊（`ChatController`、`PrivateChatController`）与
 * PM 的项目留言板（`MessageController`）都走这里，模块内不再各写一套。
 * 房间三元组：module + room_type + room_id；room_type 见 ChatMessage 常量。
 */
class ChatService
{
    public const MODULES = ['core', 'erp', 'pm', 'soho'];

    private const ROOM_TYPES = [
        ChatMessage::ROOM_GROUP,
        ChatMessage::ROOM_PROJECT,
        ChatMessage::ROOM_PRIVATE,
    ];

    // 与 ERP 群聊一致的附件白名单/大小；PM 留言板 20MB，取宽的那个
    public const MAX_FILE_KB = 20480;

    public const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];

    public function ensureModule(string $module): string
    {
        if (! in_array($module, self::MODULES, true)) {
            throw new InvalidArgumentException("未知模块：{$module}");
        }

        return $module;
    }

    public function ensureRoomType(string $roomType): string
    {
        if (! in_array($roomType, self::ROOM_TYPES, true)) {
            throw new InvalidArgumentException("未知房间类型：{$roomType}");
        }

        return $roomType;
    }

    /** 房间键（清空标记、索引都用它） */
    public function roomKey(string $roomType, int $roomId): string
    {
        return ChatClearState::keyFor($roomType, $roomId);
    }

    /**
     * 取某房间的消息（按本人「清空」标记裁剪）。
     *
     * @return Collection<int,ChatMessage>
     */
    public function listFor(
        User $viewer,
        string $module,
        string $roomType,
        int $roomId,
        int $afterId = 0,
        int $limit = 300,
    ): Collection {
        $this->ensureModule($module);
        $this->ensureRoomType($roomType);

        $query = $roomType === ChatMessage::ROOM_PRIVATE
            ? ChatMessage::between($module, $viewer->id, $roomId)
            : ChatMessage::forRoom($module, $roomType, $roomId);

        $query->with('sender')
            ->withTrashed()
            ->afterClear($viewer->id, $module, $this->roomKey($roomType, $roomId));

        if ($afterId > 0) {
            return $query->where('id', '>', $afterId)->orderBy('id')->limit($limit)->get();
        }

        return $query->orderByDesc('id')->limit($limit)->get()->reverse()->values();
    }

    /**
     * 发消息。
     *
     * @param  array<int,UploadedFile>  $files
     */
    public function send(
        User $sender,
        string $module,
        string $roomType,
        int $roomId,
        ?string $content,
        array $files = [],
        ?int $replyToId = null,
        ?int $tenantId = null,
    ): ChatMessage {
        $this->ensureModule($module);
        $this->ensureRoomType($roomType);

        $content = is_string($content) ? trim($content) : null;
        if (($content === null || $content === '') && $files === []) {
            throw new InvalidArgumentException('消息内容不能为空');
        }

        $attachments = [];
        foreach ($files as $index => $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, self::ALLOWED_EXT, true)) {
                throw new InvalidArgumentException('不支持的附件类型：'.$ext);
            }
            $path = $file->store("chat/{$module}/".date('Ymd'), 'public');
            $attachments[] = [
                'index' => count($attachments),
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getClientMimeType(),
            ];
        }

        $msgType = $attachments === [] ? 'text' : (self::looksLikeImage($attachments) ? 'image' : 'file');

        return ChatMessage::create([
            'module' => $module,
            'room_type' => $roomType,
            'room_id' => $roomId,
            'sender_id' => $sender->id,
            'content' => $content,
            'msg_type' => $msgType,
            'attachments' => $attachments ?: null,
            'reply_to_id' => $replyToId,
            'tenant_id' => $tenantId,
        ]);
    }

    /** 撤回自己的消息（软删，保留附件文件，与 ERP 一致） */
    public function deleteOwn(User $user, int $id): bool
    {
        $message = ChatMessage::find($id);
        if (! $message || (int) $message->sender_id !== (int) $user->id) {
            return false;
        }

        $message->delete();

        return true;
    }

    /** 清空我看到的记录（只影响本人） */
    public function clearMine(User $user, string $module, string $roomType, int $roomId): ChatClearState
    {
        $this->ensureModule($module);
        $this->ensureRoomType($roomType);

        return ChatClearState::markCleared($user->id, $module, $this->roomKey($roomType, $roomId));
    }

    /** 附件下载/预览 */
    public function download(User $user, int $id, int $index = 0): array
    {
        $message = ChatMessage::with('sender')->findOrFail($id);
        abort_unless((int) $message->sender_id === (int) $user->id
            || $this->canView($user, $message), 403, '无权访问该附件');

        $file = $message->attachments[$index] ?? null;
        abort_if($file === null, 404, '该消息没有附件');
        abort_unless(Storage::disk('public')->exists($file['path']), 404, '附件已不存在');

        return ['path' => $file['path'], 'name' => $file['name'] ?? basename($file['path'])];
    }

    /**
     * 能否看到这条消息。
     * 现阶段的可见性：private 只有双方；group/project 同模块内所有登录用户可见
     * （项目级细粒度权限在 B-03 项目模块迁入后由模块侧再收紧）。
     */
    public function canView(User $user, ChatMessage $message): bool
    {
        if ($message->room_type === ChatMessage::ROOM_PRIVATE) {
            return in_array((int) $user->id, [(int) $message->sender_id, (int) $message->room_id], true);
        }

        return in_array($message->module, $user->resolveModules(), true);
    }

    private static function looksLikeImage(array $attachments): bool
    {
        foreach ($attachments as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
                return true;
            }
        }

        return false;
    }
}
