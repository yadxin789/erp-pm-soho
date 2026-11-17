<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 「清空我看到的聊天记录」标记（TC-10）。
 *
 * 一个用户 + 一个模块 + 一个房间键 = 一条记录；只影响这个人的视图，
 * 真实消息不删（与 ERP ChatClearState、PM 的 chat_clear_states 语义一致）。
 */
class ChatClearState extends Model
{
    protected $fillable = ['user_id', 'module', 'room_key', 'cleared_at'];

    protected $casts = ['cleared_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 房间键：group / project:{id} / private:{对方id} */
    public static function keyFor(string $roomType, int $roomId): string
    {
        return match ($roomType) {
            ChatMessage::ROOM_GROUP => 'group',
            ChatMessage::ROOM_PROJECT => 'project:'.$roomId,
            ChatMessage::ROOM_PRIVATE => 'private:'.$roomId,
            default => $roomType.':'.$roomId,
        };
    }

    public static function markCleared(int $userId, string $module, string $roomKey): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'module' => $module, 'room_key' => $roomKey],
            ['cleared_at' => now()]
        );
    }
}
