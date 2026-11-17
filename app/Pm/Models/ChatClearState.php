<?php

namespace App\Pm\Models;

use Illuminate\Database\Eloquent\Model;

class ChatClearState extends Model
{
    /** B-03：Core/ERP 已占用 chat_clear_states（module/room_key 口径），PM 独立到 pm_chat_clear_states */
    protected $table = 'pm_chat_clear_states';

    protected $fillable = [
        'user_id',
        'project_id',
        'chat_type',
        'other_user_id',
        'conversation_key',
        'cleared_at',
    ];

    protected $casts = [
        'cleared_at' => 'datetime',
    ];
}
