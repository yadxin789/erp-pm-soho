<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 聊天留言板清空状态模型
 * 记录每个用户清空留言板的时间点，实现"软清空"：
 * - 只清空当前用户的显示记录，不影响其他用户
 * - 查询消息时过滤掉 cleared_at 之前的消息
 */
class ChatClearState extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'user_id',
        'chat_type',
        'conversation_key',
        'other_user_id',
        'cleared_at',
    ];

    protected $casts = [
        'cleared_at' => 'datetime',
    ];

    /**
     * 执行清空操作的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 私聊时的对方用户
     */
    public function otherUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'other_user_id');
    }

    /**
     * 生成私聊会话标识
     */
    public static function privateConversationKey(int $otherUserId): string
    {
        return 'user_' . $otherUserId;
    }
}
