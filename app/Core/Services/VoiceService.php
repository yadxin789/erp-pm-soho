<?php

namespace App\Core\Services;

use App\Core\Models\User;
use App\Core\Models\VoiceParticipant;
use App\Core\Models\VoiceRoom;
use App\Core\Models\VoiceSignal;
use InvalidArgumentException;

/**
 * 语音会议服务（TC-10，母版取 ERP：浏览器 WebRTC mesh + 应用内信令中转）。
 *
 * 为什么不用 PM 的 Swoole/Janus：那套要外部信令服务与 OnlyOffice 才能跑，
 * 合并后按 A 文档「语音取 ERP」——断网/内网也能开会，零外部依赖。
 * PM 的 hookEnter/hookLeave 作为**可选兼容入口**保留（见 VoiceController），
 * 有外部信令服务时仍可回写同一张表。
 */
class VoiceService
{
    public const MODULES = ['core', 'erp', 'pm', 'soho'];

    /** 心跳有效期（秒）：30 秒内有更新即视为在会（与 ERP voice_presence 同口径） */
    public const HEARTBEAT_TTL = 30;

    public const SIGNAL_TYPES = ['offer', 'answer', 'candidate', 'leave', 'heartbeat'];

    public function ensureModule(string $module): string
    {
        if (! in_array($module, self::MODULES, true)) {
            throw new InvalidArgumentException("未知模块：{$module}");
        }

        return $module;
    }

    /** 按 (module,type,room_key) 复用同一间房，没有就开 */
    public function ensureRoom(string $module, string $type, int $roomKey, User $owner, ?string $name = null): VoiceRoom
    {
        $this->ensureModule($module);

        $room = VoiceRoom::firstOrCreate(
            ['module' => $module, 'type' => $type, 'name' => $name ?: $this->defaultRoomName($type, $roomKey)],
            ['owner_id' => $owner->id, 'status' => 'open', 'started_at' => now()],
        );

        if (! $room->isOpen()) {
            $room->forceFill(['status' => 'open', 'ended_at' => null, 'started_at' => now(), 'owner_id' => $owner->id])->save();
        }

        return $room;
    }

    public function join(VoiceRoom $room, User $user, string $role = 'member'): VoiceParticipant
    {
        $participant = VoiceParticipant::firstOrNew(['room_id' => $room->id, 'user_id' => $user->id]);
        if (! $participant->exists) {
            $participant->fill(['joined_at' => now(), 'role' => $role]);
        }
        $participant->left_at = null;
        $participant->save();
        $participant->touch();   // updated_at 即心跳

        return $participant;
    }

    public function heartbeat(VoiceRoom $room, User $user): VoiceParticipant
    {
        return $this->join($room, $user);
    }

    public function leave(VoiceRoom $room, User $user): void
    {
        $participant = VoiceParticipant::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->latest('id')
            ->first();

        $participant?->forceFill(['left_at' => now()])->save();
    }

    /** 发信令（heartbeat 只刷心跳，不落信令表） */
    public function signal(VoiceRoom $room, User $user, string $type, ?array $payload = null, ?int $toUserId = null): void
    {
        if (! in_array($type, self::SIGNAL_TYPES, true)) {
            throw new InvalidArgumentException("未知信令类型：{$type}");
        }

        $this->heartbeat($room, $user);

        if ($type === 'heartbeat') {
            return;
        }

        VoiceSignal::create([
            'room_id' => $room->id,
            'from_user' => $user->id,
            'to_user' => $toUserId,
            'type' => $type,
            'payload' => $payload,
        ]);

        if ($type === 'leave') {
            $this->leave($room, $user);
        }
    }

    /**
     * 拉取发给我（或广播）的未消费信令，并打上 consumed_at（与 ERP poll 一致：只投递一次）。
     *
     * @return array{signals: array<int,array>, members: array<int,array>, room: array}
     */
    public function poll(VoiceRoom $room, User $user, int $limit = 50): array
    {
        $this->heartbeat($room, $user);

        $signals = VoiceSignal::where('room_id', $room->id)
            ->where('from_user', '!=', $user->id)
            ->where(fn ($q) => $q->whereNull('to_user')->orWhere('to_user', $user->id))
            ->whereNull('consumed_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($signals->isNotEmpty()) {
            VoiceSignal::whereIn('id', $signals->pluck('id'))->update(['consumed_at' => now()]);
        }

        return [
            'signals' => $signals->map(fn (VoiceSignal $s) => [
                'from' => (int) $s->from_user,
                'type' => $s->type,
                'payload' => $s->payload,
            ])->values()->all(),
            'members' => $this->members($room),
            'room' => [
                'id' => $room->id,
                'status' => $room->status,
                'owner_id' => (int) $room->owner_id,
                'started_at' => $room->started_at?->format('Y-m-d H:i'),
                'duration' => $room->durationLabel(),
            ],
        ];
    }

    /** 在会成员（心跳 30 秒内） */
    public function members(VoiceRoom $room): array
    {
        return $room->participants()
            ->whereNull('left_at')
            ->where('updated_at', '>=', now()->subSeconds(self::HEARTBEAT_TTL))
            ->with('user:id,name,real_name,username,email,avatar')
            ->get()
            ->map(fn (VoiceParticipant $p) => [
                'id' => (int) $p->user_id,
                'name' => $p->user?->displayName() ?? '未知用户',
                'role' => $p->role,
                'joined_at' => $p->joined_at?->format('H:i'),
                'is_owner' => (int) $p->user_id === (int) $room->owner_id,
            ])->values()->all();
    }

    /** 结束会议：写 ended_at、清掉未消费信令与在会记录（与 ERP end 一致） */
    public function close(VoiceRoom $room, ?User $by = null, array $participants = []): VoiceRoom
    {
        $room->forceFill([
            'status' => 'closed',
            'ended_at' => now(),
        ])->save();

        VoiceSignal::where('room_id', $room->id)->whereNull('consumed_at')->delete();
        VoiceParticipant::where('room_id', $room->id)->whereNull('left_at')->update(['left_at' => now()]);

        return $room;
    }

    /** 入会令牌：HMAC(room|user|ts, app.key)，前端拿它建 WebRTC 连接（对齐 ERP issueToken） */
    public function token(VoiceRoom $room, User $user): array
    {
        $ts = now()->timestamp;
        $signature = hash_hmac('sha256', "{$room->id}|{$user->id}|{$ts}", (string) config('app.key'));

        return [
            'room' => $room->id,
            'userId' => $user->id,
            'userName' => $user->displayName(),
            'ts' => $ts,
            'signature' => $signature,
            'iceServers' => [],
        ];
    }

    /** 会议历史（房间级，最近 20 场） */
    public function history(string $module, int $roomKey = 0): array
    {
        return VoiceRoom::where('module', $module)
            ->when($roomKey > 0, fn ($q) => $q->where('type', 'project')->where('name', 'like', "project-{$roomKey}%"))
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (VoiceRoom $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->status,
                'started_at' => $r->started_at?->format('Y-m-d H:i'),
                'ended_at' => $r->ended_at?->format('Y-m-d H:i'),
                'duration' => $r->durationLabel(),
                'owner' => $r->owner?->displayName(),
            ])->values()->all();
    }

    private function defaultRoomName(string $type, int $roomKey): string
    {
        return $type === 'project' ? "project-{$roomKey}" : ($type === 'group' ? 'group' : "room-{$roomKey}");
    }
}
