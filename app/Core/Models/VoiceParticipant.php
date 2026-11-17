<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 语音房在会记录（TC-10）。
 * joined_at/left_at 记在会区间；updated_at 兼作**心跳**（30 秒内有更新即视为在线）。
 */
class VoiceParticipant extends Model
{
    protected $fillable = ['room_id', 'user_id', 'joined_at', 'left_at', 'role'];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(VoiceRoom::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline(): bool
    {
        return $this->left_at === null && $this->updated_at >= now()->subSeconds(30);
    }

    public function heartbeat(): void
    {
        $this->left_at = null;
        $this->touch();
    }
}
