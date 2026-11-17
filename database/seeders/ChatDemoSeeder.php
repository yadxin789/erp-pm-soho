<?php

namespace Database\Seeders;

use App\Core\Models\ChatMessage;
use App\Core\Models\User;
use Illuminate\Database\Seeder;

/**
 * 聊天演示数据（TC-10）。
 *
 * 覆盖三种房间：模块群聊（erp / pm）、项目留言板（pm #101）、私聊（admin ↔ multi_module），
 * 用来验证「同页可读、跨模块不串、私聊双向可见」。真实历史迁移见 scripts 里的说明。
 */
class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->user('admin');
        $pm = $this->user('pm_director');
        $multi = $this->user('multi_module');
        $soho = User::where('email', 'admin@soho.demo')->first();

        if (! $admin || ! $pm || ! $multi) {
            $this->command?->warn('演示账号缺失，跳过聊天演示数据');

            return;
        }

        $rows = [
            // ERP 模块群聊（原 ERP 的「群聊」）
            ['erp', ChatMessage::ROOM_GROUP, 0, $admin, '大家早，今天的生产排程已更新到看板，注意 SMT 二线换线时间。', null],
            ['erp', ChatMessage::ROOM_GROUP, 0, $multi, '收到。销售这边今天有 3 张急单，已经同步给计划了。', null],
            ['erp', ChatMessage::ROOM_GROUP, 0, $admin, '急单走绿色通道，缺料直接找仓储赵工确认。', null],
            ['erp', ChatMessage::ROOM_GROUP, 0, $multi, '好的，那我把客户交期也回写一下。', null],

            // PM 项目留言板（原 PM 的 messages，room_id = 项目 id）
            ['pm', ChatMessage::ROOM_PROJECT, 101, $pm, '智能网关 V2 的接口评审纪要已放到项目文件，请各位今天内确认。', null],
            ['pm', ChatMessage::ROOM_PROJECT, 101, $admin, '硬件侧已确认，结构件下周到样。', null],
            ['pm', ChatMessage::ROOM_PROJECT, 101, $pm, '收到，那我把 T-118 的排期往后挪两天。', null],

            // 私聊（room_id = 对方 user id，双向可见）
            ['erp', ChatMessage::ROOM_PRIVATE, $multi->id, $admin, '那张报价单的税率你按 13% 出，别再用老模板。', null],
            ['erp', ChatMessage::ROOM_PRIVATE, $admin->id, $multi, '明白，我改完发你复核。', null],
            ['erp', ChatMessage::ROOM_PRIVATE, $multi->id, $admin, '已发，麻烦看下附件里的成本拆分。', null],
        ];

        $created = 0;
        foreach ($rows as [$module, $roomType, $roomId, $sender, $content]) {
            $exists = ChatMessage::where('module', $module)
                ->where('room_type', $roomType)
                ->where('room_id', $roomId)
                ->where('sender_id', $sender->id)
                ->where('content', $content)
                ->exists();
            if ($exists) {
                continue;
            }

            ChatMessage::create([
                'module' => $module,
                'room_type' => $roomType,
                'room_id' => $roomId,
                'sender_id' => $sender->id,
                'content' => $content,
                'msg_type' => 'text',
            ]);
            $created++;
        }

        // SOHO 模块群聊留 1 条，验证三模块都能各自开房
        if ($soho) {
            $exists = ChatMessage::where('module', 'soho')->where('content', '店铺日报模板已更新，请运营同事按新口径填报。')->exists();
            if (! $exists) {
                ChatMessage::create([
                    'module' => 'soho',
                    'room_type' => ChatMessage::ROOM_GROUP,
                    'room_id' => 0,
                    'sender_id' => $soho->id,
                    'content' => '店铺日报模板已更新，请运营同事按新口径填报。',
                    'msg_type' => 'text',
                ]);
                $created++;
            }
        }

        $this->command?->info("聊天演示数据：新增 {$created} 条");
    }

    private function user(string $account): ?User
    {
        return User::where('username', $account)->first() ?: User::where('email', $account)->first();
    }
}
