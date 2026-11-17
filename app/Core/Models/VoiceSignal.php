<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WebRTC 信令（TC-10，沿用 ERP 的「应用内中转」方案，不依赖外部信令服务）。
 *
 * type: offer / answer / candidate / leave；to_user 为空表示广播给房内所有人。
 * consumed_at 非空表示已被拉取，pull 时同一信令只投递一次。
 */
class VoiceSignal extends Model
{
    protected $fillable = ['room_id', 'from_user', 'to_user', 'type', 'payload', 'consumed_at'];

    protected $casts = [
        'payload' => 'array',
        'consumed_at' => 'datetime',
        'from_user' => 'integer',
        'to_user' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(VoiceRoom::class, 'room_id');
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user');
    }
}
