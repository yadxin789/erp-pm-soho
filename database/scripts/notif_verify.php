<?php
/**
 * TC-09 通知中心验收。
 *
 * 覆盖：
 *   1. 三源历史迁移可行性（干跑，不改库）
 *   2. 表内分布：条数 / 模块 / 已读未读
 *   3. 未读数与 grouped 统计一致（角标数据源）
 *   4. 模块过滤生效（erp/pm/soho/core 各自条数）
 *   5. 单条已读写入（用临时通知，验完即删，不动演示数据）
 *   6. 越权保护：不能读别人的通知、不能删别人的通知
 *
 * 用法：php database/scripts/notif_verify.php
 */

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\Notification;
use App\Core\Models\User;
use App\Core\Services\NotificationService;
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

$service = app(NotificationService::class);

echo "=== 1. 三源历史迁移可行性（干跑） ===\n";
$reportPath = $root.'/docs/notifications/migration-report.json';
if (is_file($reportPath)) {
    $report = json_decode(file_get_contents($reportPath), true);
    echo '  报告生成于 '.$report['generated_at'].'，name-match='.($report['allow_name_match'] ? 'on' : 'off')."\n";
    foreach (['erp', 'pm', 'soho'] as $m) {
        $s = $report['sources'][$m];
        echo sprintf("  %-4s 源 %3d 条 / 用户可映射 %3d 条\n", strtoupper($m), $s['total'], $s['matched']);
    }
    echo '  合计源 '.$report['totals']['source_rows'].' 条，强匹配可映射 '.$report['totals']['matched'].' 条（未写库）'."\n";
} else {
    echo "  报告缺失，先跑 php database/scripts/notif_migrate.php\n";
    $fail++;
}

echo "\n=== 2. 表内分布 ===\n";
$total = Notification::count();
echo "  总计 {$total} 条\n";
foreach (['core', 'erp', 'pm', 'soho'] as $m) {
    $rows = Notification::forModule($m)->count();
    $unread = Notification::forModule($m)->unread()->count();
    echo sprintf("  %-4s %3d 条（未读 %3d）\n", strtoupper($m), $rows, $unread);
}
ok($total > 0, '通知表非空', "{$total} 条");

echo "\n=== 3. 未读数与角标数据源一致 ===\n";
foreach (['admin', 'pm_director', 'multi_module'] as $account) {
    $user = User::where('username', $account)->first() ?: User::where('email', $account)->first();
    if (! $user) {
        continue;
    }
    $counts = $service->unreadByModule($user);
    $direct = Notification::where('user_id', $user->id)->unread()->count();
    ok($counts['total'] === $direct, $account.' 未读总数一致', "聚合 {$counts['total']} / 直查 {$direct}");
    $sum = $counts['core'] + $counts['erp'] + $counts['pm'] + $counts['soho'];
    ok($sum === $direct, $account.' 分模块之和等于总数', "分模块 {$sum}");
}
// 找到 soho 账号（只有 email）
$sohoUser = User::where('email', 'admin@soho.demo')->first();
if ($sohoUser) {
    $counts = $service->unreadByModule($sohoUser);
    $direct = Notification::where('user_id', $sohoUser->id)->unread()->count();
    ok($counts['total'] === $direct, 'admin@soho.demo 未读总数一致', "聚合 {$counts['total']} / 直查 {$direct}");
}

echo "\n=== 4. 模块过滤生效 ===\n";
$admin = User::where('username', 'admin')->first();
foreach (['erp', 'pm', 'soho', 'core'] as $m) {
    $viaService = $service->queryFor($admin, $m)->count();
    $viaDb = Notification::where('user_id', $admin->id)->where('module', $m)->count();
    ok($viaService === $viaDb, "admin 过滤 {$m}", "service {$viaService} / db {$viaDb}");
}
$all = $service->queryFor($admin)->count();
ok($all === Notification::where('user_id', $admin->id)->count(), 'admin 不过滤时返回全部', "{$all} 条");

$byModule = $service->totalByModule($admin);
ok($byModule['total'] === Notification::where('user_id', $admin->id)->count(), 'tab 计数之和等于该账号全部通知', "tab 合计 {$byModule['total']}");

echo "\n=== 5. 已读写入（临时通知，验完删除） ===\n";
$temp = $service->send($admin, 'core', 'verify', '[verify] 临时通知', '仅用于验收脚本', '/notifications', ['demo' => true, 'verify' => true]);
ok($temp->is_read === false, '新通知初始为未读');
$before = $service->unreadCount($admin);
$service->markRead($admin, $temp->id);
$after = $service->unreadCount($admin);
ok($after === $before - 1, '标记已读后未读数 -1', "{$before} → {$after}");
ok($temp->fresh()->read_at !== null, 'read_at 已写入');
$service->delete($admin, $temp->id);
ok(Notification::where('id', $temp->id)->exists() === false, '临时通知已删除');

echo "\n=== 6. 越权保护 ===\n";
$other = Notification::where('user_id', '!=', $admin->id)->first();
if ($other) {
    ok($service->markRead($admin, $other->id) === null, '不能标记别人的通知为已读');
    ok($service->delete($admin, $other->id) === false, '不能删除别人的通知');
} else {
    echo "  无可用于越权测试的他人通知\n";
}

echo "\n=== 7. 历史迁移遗留（如实记录） ===\n";
$srcTotal = $report['totals']['source_rows'] ?? 0;
$srcMatched = $report['totals']['matched'] ?? 0;
echo "  三源真实历史共 {$srcTotal} 条；按 email/username 强匹配可挂到现有账号的 {$srcMatched} 条。\n";
echo "  其余源账号在合并库里还不存在——用户迁移属 B-05。当前库里 {$total} 条为 NotificationDemoSeeder 演示数据。\n";
echo "  B-05 完成后执行 `php database/scripts/notif_migrate.php --apply` 即可把历史挂到正确账号。\n";

echo "\n".($fail === 0 ? "结论：全部通过（历史迁移待 B-05）\n" : "结论：❌ {$fail} 项未通过\n");
exit($fail === 0 ? 0 : 1);
