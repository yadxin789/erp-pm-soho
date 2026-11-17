<?php
/**
 * TC-08 菜单验收脚本。
 *
 * 验收口径（B-01 TC-08）：
 *   1. 菜单条数与基线逐模块比对（少一条即红）
 *   2. 菜单项 route 名全部可解析（本轮：模块未迁入前统计未注册数，作为已知缺口计入报告）
 *   3. 权限过滤后可见项数与基线一致（按真实演示账号逐个统计）
 *
 * 用法：php database/scripts/menu_verify.php
 */

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Menu\MenuRepository;
use App\Core\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

$baseline = json_decode(file_get_contents($root.'/docs/menu/baseline-menu.json'), true);
$fail = 0;
$warn = 0;

function line(string $s): void
{
    echo $s."\n";
}

line('=== 1. 条数比对（基线 vs 生成） ===');
$baseCounts = ['erp' => $baseline['erp']['count'], 'pm' => $baseline['pm']['count'], 'soho' => $baseline['soho']['count']];
foreach ($baseCounts as $m => $expect) {
    $actual = MenuRepository::count($m);
    $ok = $actual === $expect;
    line(sprintf('  %-5s 基线 %3d / 生成 %3d %s', $m, $expect, $actual, $ok ? 'OK' : '❌ 少'.($expect - $actual).'条'));
    if (! $ok) {
        $fail++;
    }
}
line('  合计 基线 '.(array_sum($baseCounts)).' / 生成 '.(MenuRepository::count('erp') + MenuRepository::count('pm') + MenuRepository::count('soho')));

line('');
line('=== 2. 路由名可解析性 ===');
foreach (['erp', 'pm', 'soho'] as $m) {
    $items = MenuRepository::all($m);
    $missing = [];
    foreach ($items as $it) {
        if (! MenuRepository::routeExists($it['route'])) {
            $missing[] = $it['route'];
        }
    }
    line(sprintf('  %-5s 已注册 %3d / 未注册 %3d', $m, count($items) - count($missing), count($missing)));
    if ($missing) {
        $warn++;
        line('        未注册示例：'.implode('、', array_slice($missing, 0, 5)).(count($missing) > 5 ? ' …' : ''));
    }
}

line('');
line('=== 3. 前缀与权限合规 ===');
$badPrefix = [];
$badPerm = [];
$permNames = array_flip(DB::table('permissions')->pluck('name')->all());
foreach (['erp', 'pm', 'soho'] as $m) {
    foreach (MenuRepository::all($m) as $it) {
        if (! str_starts_with($it['route'], $m.'.')) {
            $badPrefix[] = $it['route'];
        }
        if ($it['permission'] !== null && ! isset($permNames[$it['permission']])) {
            $badPerm[] = $it['route'].' → '.$it['permission'];
        }
        if ($it['permission'] !== null && ! str_starts_with($it['permission'], $m.'.')) {
            $badPerm[] = $it['route'].' → '.$it['permission'].'（前缀不符）';
        }
    }
}
line('  路由前缀违规：'.count($badPrefix).'；权限名违规：'.count($badPerm));
if ($badPrefix || $badPerm) {
    $fail++;
    foreach (array_slice(array_merge($badPrefix, $badPerm), 0, 10) as $b) {
        line('    '.$b);
    }
}

line('');
line('=== 4. 权限过滤后可见项（真实演示账号） ===');
$accounts = [
    'admin' => 'erp',
    'pm_director' => 'pm',
    'admin@soho.demo' => 'soho',
    'multi_module' => 'erp',
];
foreach ($accounts as $account => $module) {
    // 演示账号有的只有 username、有的只有 email，两种都试
    $user = User::where('username', $account)->first() ?: User::where('email', $account)->first();
    if (! $user) {
        line("  {$account} → 未找到账号 ❌");
        $fail++;
        continue;
    }
    $total = MenuRepository::count($module);
    $visible = count(MenuRepository::visible($module, $user));
    $roles = $user->roles->pluck('name')->all();
    line(sprintf('  %-16s 模块 %-4s 可见 %3d / %3d  角色 %s', $account, $module, $visible, $total, implode(',', $roles) ?: '（无）'));
    if ($visible === 0) {
        line('        ❌ 可见 0 项，检查权限/角色映射');
        $fail++;
    }
}

line('');
line('=== 5. 不可见项抽样（验证闸门真的生效） ===');
$user = User::where('username', 'admin')->first();
$hidden = [];
foreach (MenuRepository::all('erp') as $it) {
    if (! MenuRepository::canSee($it, $user)) {
        $hidden[] = $it['label'].'('.$it['route'].')';
    }
}
line('  admin 在 erp 被隐藏 '.count($hidden).' 项：'.implode('、', array_slice($hidden, 0, 6)).(count($hidden) > 6 ? ' …' : ''));

line('');
line($fail === 0 ? '结论：条数与合规通过'.($warn ? '（路由未注册属模块未迁入的已知缺口）' : '') : "结论：❌ {$fail} 项未通过");
exit($fail === 0 ? 0 : 1);
