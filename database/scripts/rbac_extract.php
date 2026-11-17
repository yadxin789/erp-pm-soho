<?php
/**
 * TC-07 基线抽取：把三套角色/权限原始数据导出成一份 JSON（只读源库，不改源数据）。
 *
 * 用法：php database/scripts/rbac_extract.php
 * 输出：docs/rbac/baseline-rbac.json
 */

$root = dirname(__DIR__, 2);
// 不用 parse_ini_file：.env 里密码可能含 # 或引号，ini 解析会报错（坑 21）
$dbPass = (string) getenv('DBP');
if ($dbPass === '') {
    foreach (file($root.'/.env', FILE_IGNORE_NEW_LINES) as $line) {
        if (str_starts_with(trim($line), 'DB_PASSWORD=')) {
            $dbPass = trim(substr(trim($line), strlen('DB_PASSWORD=')), " \t\"'");
            break;
        }
    }
}

function mysqlPdo(string $db, string $pass): PDO
{
    return new PDO("mysql:host=127.0.0.1;dbname={$db};charset=utf8mb4", 'root', $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_CASE    => PDO::CASE_LOWER,
    ]);
}

$out = ['generated_at' => date('c'), 'sources' => []];

// ---------- ERP（MySQL dedao_erp） ----------
$p = mysqlPdo('dedao_erp', $dbPass);
$erpPerms = [];
foreach ($p->query('SELECT name FROM permissions ORDER BY name') as $r) {
    $erpPerms[] = $r['name'];
}
$erpRoles = [];
foreach ($p->query('SELECT id, name, guard_name FROM roles ORDER BY id') as $r) {
    $map = [];
    $q = $p->prepare('SELECT p.name FROM role_has_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? ORDER BY p.name');
    $q->execute([$r['id']]);
    foreach ($q as $row) {
        $map[] = $row['name'];
    }
    $uc = $p->prepare('SELECT COUNT(*) c FROM model_has_roles WHERE role_id = ?');
    $uc->execute([$r['id']]);
    $erpRoles[] = [
        'id'          => (int) $r['id'],
        'name'        => $r['name'],
        'guard'       => $r['guard_name'],
        'permissions' => $map,
        'users'       => (int) $uc->fetch()['c'],
    ];
}
$out['sources']['erp'] = ['permissions' => $erpPerms, 'roles' => $erpRoles];

// ---------- PM（MySQL pm_db） ----------
$q = mysqlPdo('pm_db', $dbPass);
$pmRoles = [];
foreach ($q->query('SELECT id, name, code, description FROM roles ORDER BY id') as $r) {
    $uc = $q->prepare('SELECT COUNT(*) c FROM model_has_roles WHERE role_id = ?');
    $uc->execute([$r['id']]);
    $pmRoles[] = [
        'id'          => (int) $r['id'],
        'name'        => $r['name'],
        'code'        => $r['code'],
        'description' => $r['description'],
        'users'       => (int) $uc->fetch()['c'],
    ];
}
$pmPermCount = (int) $q->query('SELECT COUNT(*) c FROM permissions')->fetch()['c'];
$out['sources']['pm'] = ['permissions' => [], 'permission_rows_in_source' => $pmPermCount, 'roles' => $pmRoles];

// ---------- SOHO（SQLite 降级副本） ----------
$sqlitePath = 'E:/_tmp_soho_baseline/database/database.sqlite';
$s = new PDO('sqlite:'.$sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_CASE    => PDO::CASE_LOWER,
]);
$sohoPerms = [];
foreach ($s->query('SELECT name FROM permissions ORDER BY name') as $r) {
    $sohoPerms[] = $r['name'];
}
$sohoRoles = [];
foreach ($s->query('SELECT id, name, guard_name FROM roles ORDER BY id') as $r) {
    $map = [];
    $st = $s->prepare('SELECT p.name FROM role_has_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? ORDER BY p.name');
    $st->execute([$r['id']]);
    foreach ($st as $row) {
        $map[] = $row['name'];
    }
    $uc = $s->prepare('SELECT COUNT(*) c FROM model_has_roles WHERE role_id = ?');
    $uc->execute([$r['id']]);
    $sohoRoles[] = [
        'id'          => (int) $r['id'],
        'name'        => $r['name'],
        'guard'       => $r['guard_name'],
        'permissions' => $map,
        'users'       => (int) $uc->fetch()['c'],
    ];
}
$out['sources']['soho'] = ['permissions' => $sohoPerms, 'roles' => $sohoRoles];

$dir = $root.'/docs/rbac';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}
file_put_contents(
    $dir.'/baseline-rbac.json',
    json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);

echo "已导出 docs/rbac/baseline-rbac.json\n";
echo '  ERP: '.count($erpPerms).' 权限 / '.count($erpRoles)." 角色\n";
echo '  PM:  0 权限 / '.count($pmRoles)." 角色（源库 permission 行数 {$pmPermCount}）\n";
echo '  SOHO: '.count($sohoPerms).' 权限 / '.count($sohoRoles)." 角色\n";
