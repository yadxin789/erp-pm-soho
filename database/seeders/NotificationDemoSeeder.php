<?php

namespace Database\Seeders;

use App\Core\Models\User;
use App\Core\Services\NotificationService;
use Illuminate\Database\Seeder;

/**
 * 通知中心演示数据（TC-09）。
 *
 * 说明：这**不是**迁移三源历史通知，而是为了让通知中心、未读角标、模块过滤
 * 在真实界面上可验证而播的演示数据（历史迁移见 database/scripts/notif_migrate.php，
 * 那条链路依赖 B-05 的用户迁移先完成）。每条都带 `demo` 键，便于事后清理。
 */
class NotificationDemoSeeder extends Seeder
{
    public function run(NotificationService $service): void
    {
        $accounts = [
            'admin' => [
                ['erp', 'approval', '采购订单 PO-2026-0087 待你审批', '赵仓储提交的采购订单金额 ¥12,480，需生产总监复核后放行。', '/erp/approvals', 'high'],
                ['erp', 'inventory', '物料 M-3012 库存低于安全线', '当前可用 42 件，安全库存 120 件，建议立即补货。', '/erp/inventory', 'normal'],
                ['erp', 'sales', '销售订单 SO-2026-1103 已分配给你', '客户：华强电子；交期 2026-11-25。', '/erp/sales', 'normal'],
            ],
            'pm_director' => [
                ['pm', 'task', '有 3 个任务待你审核', '含 2 个设计评审、1 个延期申请。', '/pm/tasks', 'high'],
                ['pm', 'project', '项目「智能网关 V2」进度落后 4 天', '关键路径任务 T-118 未按计划完成。', '/pm/projects', 'normal'],
            ],
            'admin@soho.demo' => [
                ['soho', 'order', '店铺「华南旗舰店」新增 12 笔订单', '其中 3 笔为加急发货。', '/soho/order', 'normal'],
                ['soho', 'refund', '退款申请 RF-2026-0451 待审批', '订单号 SO-2026-0988，金额 ¥368。', '/soho/after-sale', 'high'],
            ],
            'multi_module' => [
                ['erp', 'sales', '报价单 QT-2026-0231 客户已查看', '客户：德赛实业；报价有效期 7 天。', '/erp/sales', 'normal'],
                ['soho', 'inventory', '平台 SKU 映射有 5 条待确认', '涉及店铺：亚马逊 US、Shopee SG。', '/soho/platform-sku', 'normal'],
            ],
        ];

        $seen = 0;
        foreach ($accounts as $account => $rows) {
            $user = User::where('username', $account)->first() ?: User::where('email', $account)->first();
            if (! $user) {
                $this->command?->warn("跳过：找不到演示账号 {$account}");
                continue;
            }

            // 幂等：同一个账号同标题的通知不重复插入
            foreach ($rows as $index => [$module, $type, $title, $content, $link, $priority]) {
                $exists = \App\Core\Models\Notification::where('user_id', $user->id)->where('title', $title)->exists();
                if ($exists) {
                    continue;
                }

                $notification = $service->send(
                    $user,
                    $module,
                    $type,
                    $title,
                    $content,
                    $link,
                    ['demo' => true, 'seed' => 'NotificationDemoSeeder'],
                    $priority,
                );
                $seen++;

                // 让「已读/未读」两种状态都在界面上出现：每账号第 2 条标为已读
                if ($index === 1) {
                    $notification->markAsRead();
                }
            }
        }

        $this->command?->info("通知演示数据：新增 {$seen} 条");
    }
}
