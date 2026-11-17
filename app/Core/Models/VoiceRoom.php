<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 语音房（TC-10，母版取 ERP 的自带走信令实现）。
 *
 * type: project（项目会，room_key=项目id）/ group（模块群会）/ adhoc（临时房）
 * status: open / closed。同一 room_key + module 复用同一间房，不重复开房。
 */
class VoiceRoom extends Model
{
    protected $fillable = ['module', 'name', 'type', 'owner_id', 'status', 'started_at', 'ended_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(VoiceParticipant::class, 'room_id');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(VoiceSignal::class, 'room_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** 当前在会的人（心跳 30 秒内） */
    public function onlineParticipants()
    {
        return $this->participants()
            ->whereNull('left_at')
            ->where('updated_at', '>=', now()->subSeconds(30));
    }

    public function durationLabel(): string
    {
        if (! $this->started_at) {
            return '—';
        }
        $end = $this->ended_at ?: now();
        $seconds = $this->started_at->diffInSeconds($end);

        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
