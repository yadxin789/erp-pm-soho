<?php
/**
 * TC-07 验收：把 Core 库里的角色/权限与 docs/rbac/baseline-rbac.json 逐角色比对。
 *
 * 输出：每模块角色数、每角色权限数、缺失/多出明细、前缀合规检查、抽样 can() 断言。
 */

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$baseline = json_decode(file_get_contents(dirname(__DIR__, 2).'/docs/rbac/baseline-rbac.json'), true);
$fail = 0;

echo "== 总量\n";
echo '  permissions: '.Permission::count()." （按模块组）\n";
foreach (DB::table('permissions')->selectRaw('`group`, COUNT(*) c')->groupBy('group')->get() as $r) {
    echo '    '.$r->group.': '.$r->c."\n";
}
echo '  roles: '.Role::count()."\n";
foreach (DB::table('roles')->selectRaw('`group`, COUNT(*) c')->groupBy('group')->get() as $r) {
    echo '    '.$r->group.': '.$r->c."\n";
}

echo "\n== 逐角色比对（源 vs Core）\n";
foreach (['erp' => 'ERP', 'soho' => 'SOHO'] as $key => $label) {
    foreach ($baseline['sources'][$key]['roles'] as $srcRole) {
        $coreName = $key.'.'.$srcRole['name'];
        $core = Role::where('name', $coreName)->where('guard_name', 'web')->first();
        if (! $core) {
            echo "  [缺失角色] {$coreName}\n";
            $fail++;
            continue;
        }
        $srcCount = count($srcRole['permissions']);
        $coreCount = $core->permissions()->count();
        $flag = $srcCount === $coreCount ? 'OK' : '差异';
        if ($srcCount !== $coreCount) {
            $fail++;
        }
        echo "  {$flag} {$label} {$srcRole['name']}: 源 {$srcCount} / Core {$coreCount}\n";
    }
}

echo "\n== PM 角色权限数（源库 0，属新建）\n";
foreach (Role::where('name', 'like', 'pm.%')->orderBy('name')->get() as $r) {
    echo '  '.$r->name.': '.$r->permissions()->count()."\n";
}

echo "\n== 前缀合规\n";
$bad = Permission::where(function ($q) {
    $q->where('name', 'not like', 'erp.%')
        ->where('name', 'not like', 'soho.%')
        ->where('name', 'not like', 'pm.%')
        ->where('name', 'not like', 'core.%');
})->count();
echo '  无前缀权限: '.$bad."\n";
if ($bad > 0) {
    $fail++;
}
$badRole = Role::where(function ($q) {
    $q->where('name', 'not like', 'erp.%')
        ->where('name', 'not like', 'soho.%')
        ->where('name', 'not like', 'pm.%')
        ->where('name', 'not like', 'core.%');
})->count();
echo '  无前缀角色: '.$badRole."\n";
if ($badRole > 0) {
    $fail++;
}
echo '  guard != web 的权限: '.Permission::where('guard_name', '!=', 'web')->count()."\n";

echo "\n== 抽样 can() 断言\n";
$cases = [
    ['admin', 'erp.sales_orders.view', true],
    ['admin', 'erp.purchase_orders.approve', true],
    ['admin', 'soho.order.view', false],
    ['admin@soho.demo', 'soho.user.view', true],
    ['admin@soho.demo', 'soho.order.view', false],
    ['admin@soho.demo', 'erp.sales_orders.view', false],
    ['pm_director', 'pm.project.view', true],
    ['pm_director', 'pm.project.create', false],
    ['pm_director', 'pm.task.approve', true],
];
foreach ($cases as [$ident, $perm, $expect]) {
    $user = User::where('username', $ident)->orWhere('email', $ident)->first();
    if (! $user) {
        echo "  [缺用户] {$ident}\n";
        $fail++;
        continue;
    }
    $actual = $user->can($perm);
    $flag = $actual === $expect ? 'OK' : 'FAIL';
    if ($actual !== $expect) {
        $fail++;
    }
    echo "  {$flag} {$ident} can({$perm}) = ".($actual ? 'true' : 'false')."（期望 ".($expect ? 'true' : 'false')."）\n";
}

echo "\n结论：".($fail === 0 ? '全部通过' : "{$fail} 项不一致")."\n";
