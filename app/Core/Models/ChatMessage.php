<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 统一聊天消息（TC-10）。
 *
 * 三源合并后只留这一张表：ERP 的群聊/私聊、PM 的项目留言板都落到这里。
 *   room_type = group   室 id 用 room_id（模块级大群，room_id 记 0 或群标识）
 *   room_type = project PM 项目留言板，room_id = 项目 id
 *   room_type = private ERP 私聊，room_id = 对方 user id（双向取并）
 *
 * module 是**隔离维度**：跨模块不串消息（ERP 群看不到 PM 项目留言）。
 */
class ChatMessage extends Model
{
    use SoftDeletes;

    public const ROOM_GROUP = 'group';

    public const ROOM_PROJECT = 'project';

    public const ROOM_PRIVATE = 'private';

    protected $fillable = [
        'module',
        'room_type',
        'room_id',
        'sender_id',
        'content',
        'msg_type',
        'attachments',
        'reply_to_id',
        'tenant_id',
    ];

    protected $casts = [
        'attachments' => 'array',
        'room_id' => 'integer',
        'reply_to_id' => 'integer',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** 某个房间的消息 */
    public function scopeForRoom(Builder $q, string $module, string $roomType, int $roomId): Builder
    {
        return $q->where('module', $module)->where('room_type', $roomType)->where('room_id', $roomId);
    }

    /** 两人私聊（双向） */
    public function scopeBetween(Builder $q, string $module, int $a, int $b): Builder
    {
        return $q->where('module', $module)->where('room_type', self::ROOM_PRIVATE)
            ->where(function ($q) use ($a, $b) {
                $q->where(fn ($s) => $s->where('sender_id', $a)->where('room_id', $b))
                    ->orWhere(fn ($s) => $s->where('sender_id', $b)->where('room_id', $a));
            });
    }

    /** 应用「清空我看到的记录」：只影响本人视图，不删真实数据 */
    public function scopeAfterClear(Builder $q, int $userId, string $module, string $roomKey): Builder
    {
        $clearedAt = ChatClearState::query()
            ->where('user_id', $userId)
            ->where('module', $module)
            ->where('room_key', $roomKey)
            ->value('cleared_at');

        return $clearedAt ? $q->where('created_at', '>', $clearedAt) : $q;
    }

    public function hasAttachments(): bool
    {
        return ! empty($this->attachments);
    }

    public function isImage(): bool
    {
        foreach ($this->attachments ?? [] as $file) {
            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
                return true;
            }
        }

        return false;
    }

    /** 前台统一结构（视图与 JSON 共用，对齐 ERP 的 toFrontend） */
    public function toFrontend(int $viewerId): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender?->displayName() ?? '未知用户',
            'is_my_message' => (int) $this->sender_id === $viewerId,
            'module' => $this->module,
            'msg_type' => $this->msg_type,
            'attachments' => array_map(fn ($f) => [
                'name' => $f['name'] ?? '',
                'size' => $f['size'] ?? 0,
                'url' => route('core.chat.file', ['id' => $this->id, 'index' => $f['index'] ?? 0]),
            ], $this->attachments ?? []),
            'is_image' => $this->isImage(),
            'reply_to_id' => $this->reply_to_id,
            'created_at' => $this->created_at?->format('H:i'),
            'full_time' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
