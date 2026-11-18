<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 聊天室消息模型
 * 管理群聊消息和私聊消息
 */
class ChatMessage extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'is_private',
        'content',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * 发送消息的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 接收者（私聊时使用）
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * 只查询群聊消息
     */
    public function scopeGroup($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * 只查询私聊消息
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * 查询两个用户之间的私聊消息
     */
    public function scopeBetween($query, $userId1, $userId2)
    {
        return $query->private()->where(function ($q) use ($userId1, $userId2) {
            $q->where(function ($sub) use ($userId1, $userId2) {
                $sub->where('user_id', $userId1)->where('receiver_id', $userId2);
            })->orWhere(function ($sub) use ($userId1, $userId2) {
                $sub->where('user_id', $userId2)->where('receiver_id', $userId1);
            });
        });
    }
}
