<?php
/**
 * TC-10 聊天与语音验收。
 *
 * 覆盖：
 *   1. 三房间类型都有数据（模块群聊 / 项目留言板 / 私聊）
 *   2. 模块隔离：erp 群聊里看不到 pm/soho 的消息
 *   3. 私聊双向：A 发给 B 的消息，B 用同一房间参数也能看到
 *   4. 「清空我的记录」只影响本人，不影响对方
 *   5. 发消息 → 轮询 after 只拿到新增
 *   6. 撤回只允许本人
 *   7. 语音：建房/入会/心跳在线/信令一次性投递/结束清场
 *
 * 用法：php database/scripts/chat_verify.php
 */

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\ChatMessage;
use App\Core\Models\User;
use App\Core\Services\ChatService;
use App\Core\Services\VoiceService;
use Illuminate\Support\Facades\DB;

$fail = 0;
function ok(bool $cond, string $msg, string $detail = ''): void
{
    global $fail;
    if (! $cond) {
        $fail++;
    }
    echo ($cond ? '  OK  ' : '  ❌  ').$msg.($detail !== '' ? '  → '.$detail : '')."\n";
}

$chat = app(ChatService::class);
$voice = app(VoiceService::class);

$admin = User::where('username', 'admin')->first();
$pm = User::where('username', 'pm_director')->first();
$multi = User::where('username', 'multi_module')->first();

echo "=== 1. 房间分布 ===\n";
$total = ChatMessage::withTrashed()->count();
echo "  总计 {$total} 条\n";
foreach (['core', 'erp', 'pm', 'soho'] as $m) {
    $rows = ChatMessage::withTrashed()->where('module', $m)->count();
    if ($rows > 0) {
        echo sprintf("  %-4s %3d 条\n", strtoupper($m), $rows);
    }
}
foreach ([['group', 0], ['project', 101]] as [$type, $id]) {
    ok($chat->listFor($admin, $type === 'group' ? 'erp' : 'pm', $type, $id)->isNotEmpty(), "房间 {$type}/{$id} 有消息");
}
ok($chat->listFor($admin, 'erp', ChatMessage::ROOM_PRIVATE, $multi->id)->isNotEmpty(), '私聊房间有消息');

echo "\n=== 2. 模块隔离 ===\n";
$erpGroup = $chat->listFor($admin, 'erp', ChatMessage::ROOM_GROUP, 0);
ok($erpGroup->every(fn ($m) => $m->module === 'erp'), 'erp 群聊里只有 erp 消息', $erpGroup->pluck('module')->unique()->implode('/'));
$pmGroup = $chat->listFor($multi, 'pm', ChatMessage::ROOM_PROJECT, 101);
ok($pmGroup->every(fn ($m) => $m->module === 'pm'), 'pm 留言板里只有 pm 消息', $pmGroup->pluck('module')->unique()->implode('/'));
$cross = ChatMessage::where('module', 'erp')->where('room_type', 'project')->count();
ok($cross === 0, '不存在跨模块误落（erp 表里没有 project 房）');

echo "\n=== 3. 私聊双向 ===\n";
$fromMulti = $chat->listFor($multi, 'erp', ChatMessage::ROOM_PRIVATE, $admin->id);
$fromAdmin = $chat->listFor($admin, 'erp', ChatMessage::ROOM_PRIVATE, $multi->id);
ok($fromMulti->count() === $fromAdmin->count(), '双方看到的私聊条数一致', "multi {$fromMulti->count()} / admin {$fromAdmin->count()}");
ok($fromMulti->pluck('id')->sort()->values()->all() === $fromAdmin->pluck('id')->sort()->values()->all(), '双方看到的消息集合一致');

echo "\n=== 4. 清空我的记录只影响本人 ===\n";
$beforeAdmin = $chat->listFor($admin, 'erp', ChatMessage::ROOM_GROUP, 0)->count();
$chat->clearMine($multi, 'erp', ChatMessage::ROOM_GROUP, 0);
$afterAdmin = $chat->listFor($admin, 'erp', ChatMessage::ROOM_GROUP, 0)->count();
$afterMulti = $chat->listFor($multi, 'erp', ChatMessage::ROOM_GROUP, 0)->count();
ok($afterAdmin === $beforeAdmin, 'admin 视角不受影响', "{$beforeAdmin} → {$afterAdmin}");
ok($afterMulti === 0, 'multi_module 视角已清空', "{$afterMulti} 条");
DB::table('chat_clear_states')->where('user_id', $multi->id)->delete();  // 复原演示数据

echo "\n=== 5. 发消息与增量轮询 ===\n";
$before = ChatMessage::where('module', 'erp')->where('room_type', ChatMessage::ROOM_GROUP)->max('id') ?? 0;
$sent = $chat->send($admin, 'erp', ChatMessage::ROOM_GROUP, 0, '[verify] 验收临时消息');
ok($sent->exists, '写入成功', "#{$sent->id}");
$after = $chat->listFor($admin, 'erp', ChatMessage::ROOM_GROUP, 0, $before);
ok($after->count() === 1 && $after->first()->id === $sent->id, 'after 参数只返回新增', "count={$after->count()}");
ok($sent->delete() === true, '本人可撤回');
ok(ChatMessage::withTrashed()->find($sent->id)->trashed(), '撤回为软删（保留记录）');
$sent->forceDelete();  // 验收痕迹不留在演示数据里
$otherSend = $chat->send($pm, 'erp', ChatMessage::ROOM_GROUP, 0, '[verify] 他人消息');
ok($chat->deleteOwn($admin, $otherSend->id) === false, '不能撤回别人的消息');
$otherSend->forceDelete();

echo "\n=== 6. 语音：建房 / 在会 / 信令一次性投递 / 结束 ===\n";
$room = $voice->ensureRoom('erp', 'group', 0, $admin, 'verify-room');
ok($room->isOpen(), '房间已开', "#{$room->id} {$room->name}");
$voice->join($room, $admin);
$voice->join($room, $multi);
ok(count($voice->members($room)) === 2, '两名成员在会', '成员 '.count($voice->members($room)));

$voice->signal($room, $admin, 'offer', ['sdp' => 'fake-offer'], $multi->id);
$poll1 = $voice->poll($room, $multi);
ok(count($poll1['signals']) === 1 && $poll1['signals'][0]['type'] === 'offer', '对方拉到 1 条 offer');
$poll2 = $voice->poll($room, $multi);
ok(count($poll2['signals']) === 0, '同一信令只投递一次（已打 consumed_at）');
ok($voice->poll($room, $admin)['signals'] === [], '发信人不会收到自己发的信令');

$voice->leave($room, $multi);
ok(count($voice->members($room)) === 1, '离会后只剩 1 人');

$voice->close($room);
ok($room->fresh()->status === 'closed' && $room->fresh()->ended_at !== null, '结束会议写了 ended_at');
ok(DB::table('voice_participants')->where('room_id', $room->id)->whereNull('left_at')->count() === 0, '结束后所有在会记录已闭合');
ok(DB::table('voice_signals')->where('room_id', $room->id)->whereNull('consumed_at')->count() === 0, '未消费信令已清理');

$token = $voice->token($room, $admin);
ok(strlen($token['signature']) === 64, '入会令牌签名长度正确（HMAC-SHA256）');

// 清理验收房
DB::table('voice_participants')->where('room_id', $room->id)->delete();
DB::table('voice_signals')->where('room_id', $room->id)->delete();
$room->delete();

echo "\n=== 7. 三源实现差异（如实记录） ===\n";
echo "  ERP 群聊/私聊：is_private + receiver_id 两个布尔/ID 表达房间 → Core 用 room_type + room_id 归一；\n";
echo "  PM 留言板：project_id 挂在消息表上 → Core 用 room_type=project、room_id=项目 id；\n";
echo "  PM 语音原本依赖 Swoole/Janus 外部信令（config/voice.*），合并后按 A 文档「语音取 ERP」改用站内中转，\n";
echo "  PM 的 hookEnter/hookLeave 保留为 /voice/hook/*，有外部信令服务时仍可回写同一张表。\n";

echo "\n".($fail === 0 ? "结论：全部通过\n" : "结论：❌ {$fail} 项未通过\n");
exit($fail === 0 ? 0 : 1);
