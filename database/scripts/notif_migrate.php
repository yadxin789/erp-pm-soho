<?php
/**
 * 三源历史通知迁移（TC-09）。
 *
 * 真实规则：
 *   erp notifications → email 优先、其次 name 匹配合并后的 users
 *   pm  notifications → username 优先、其次 name 匹配
 *   soho notifications → email 匹配（只迁 user_type in (admin,user) 的记录）
 * 字段映射：erp message→content、data→data、read_at；pm content、is_read/sender_id；
 *          soho content、data_json→data、priority、tenant_id。
 *
 * 幂等：每条写入 data.source = "erp:12"，重复执行不会重复插入。
 * 用法：
 *   php database/scripts/notif_migrate.php            # 干跑，只出报告
 *   php database/scripts/notif_migrate.php --apply    # 真写库
 */

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\Notification;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv ?? [], true);
$allowName = in_array('--name-match', $argv ?? [], true);

function dbPassword(): string
{
    $env = $GLOBALS['root'].'/.env';
    foreach (file($env) as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'DB_PASSWORD=')) {
            return trim(substr($line, 12), '"\'');
        }
    }

    return '';
}

function mysql(): PDO
{
    static $pdo;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', dbPassword(), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

/** 合并库里的用户索引：email/username/name → id */
function userIndex(): array
{
    $idx = ['email' => [], 'username' => [], 'name' => []];
    foreach (User::withTrashed()->get(['id', 'email', 'username', 'name']) as $u) {
        if ($u->email) {
            $idx['email'][mb_strtolower(trim($u->email))] = $u->id;
        }
        if ($u->username) {
            $idx['username'][mb_strtolower(trim($u->username))] = $u->id;
        }
        if ($u->name) {
            $idx['name'][trim($u->name)][] = $u->id;
        }
    }

    return $idx;
}

/**
 * 解析源用户 → 合并库用户。
 * 返回 [id|null, 匹配依据]。email/username 视为强匹配；name 仅在同一姓名唯一时才算，
 * 且必须显式 --name-match 才启用（三源同名不同人很常见，不能默认按名字合并）。
 */
function resolveUser(array $idx, ?string $email, ?string $username, ?string $name, bool $allowName): array
{
    if ($email && isset($idx['email'][mb_strtolower(trim($email))])) {
        return [$idx['email'][mb_strtolower(trim($email))], 'email'];
    }
    if ($username && isset($idx['username'][mb_strtolower(trim($username))])) {
        return [$idx['username'][mb_strtolower(trim($username))], 'username'];
    }
    if ($allowName && $name && isset($idx['name'][trim($name)])) {
        $hit = $idx['name'][trim($name)];
        if (is_array($hit) && count($hit) === 1) {
            return [$hit[0], 'name(弱匹配)'];
        }
        if (is_int($hit)) {
            return [$hit, 'name(弱匹配)'];
        }
    }

    return [null, null];
}

$idx = userIndex();
$report = ['generated_at' => date('Y-m-d H:i:s'), 'apply' => $apply, 'allow_name_match' => $allowName, 'match_by' => [], 'sources' => []];
$inserted = 0;
$skipped = 0;

// ---------- ERP ----------
$erpRows = mysql()->query('SELECT * FROM dedao_erp.notifications ORDER BY id')->fetchAll();
$erpUsers = [];
foreach (mysql()->query('SELECT id, name, email FROM dedao_erp.users') as $u) {
    $erpUsers[$u['id']] = $u;
}
$erpStat = ['total' => count($erpRows), 'matched' => 0, 'inserted' => 0, 'unmatched_users' => []];
foreach ($erpRows as $row) {
    $src = $erpUsers[$row['user_id']] ?? null;
    [$uid, $how] = $src ? resolveUser($idx, $src['email'] ?? null, null, $src['name'] ?? null, $allowName) : [null, null];
    if ($uid === null) {
        $erpStat['unmatched_users'][] = ($row['user_id'] ?? '?').':'.($src['name'] ?? '-').'/'.($src['email'] ?? '-');
        $skipped++;
        continue;
    }
    $erpStat['matched']++;
    $report['match_by'][$how] = ($report['match_by'][$how] ?? 0) + 1;

    $payload = [
        'user_id' => $uid,
        'module' => 'erp',
        'type' => $row['type'] ?: 'system',
        'title' => mb_substr((string) $row['title'], 0, 200),
        'content' => $row['message'] ?? null,
        'data' => json_encode(array_merge(
            (($d = json_decode((string) ($row['data'] ?? ''), true)) && is_array($d) ? $d : []),
            ['source' => 'erp:'.$row['id']]
        ), JSON_UNESCAPED_UNICODE),
        'link' => $row['link'] ?? null,
        'priority' => 'normal',
        'is_read' => $row['read_at'] ? 1 : 0,
        'read_at' => $row['read_at'] ?: null,
        'created_at' => $row['created_at'] ?: now(),
        'updated_at' => $row['updated_at'] ?: now(),
    ];

    if ($apply && insertOnce($payload)) {
        $erpStat['inserted']++;
        $inserted++;
    }
}
$report['sources']['erp'] = $erpStat;

// ---------- PM ----------
$pmRows = mysql()->query('SELECT * FROM pm_db.notifications WHERE deleted_at IS NULL ORDER BY id')->fetchAll();
$pmUsers = [];
foreach (mysql()->query('SELECT id, username, name FROM pm_db.users') as $u) {
    $pmUsers[$u['id']] = $u;
}
$pmStat = ['total' => count($pmRows), 'matched' => 0, 'inserted' => 0, 'unmatched_users' => []];
foreach ($pmRows as $row) {
    $src = $pmUsers[$row['user_id']] ?? null;
    [$uid, $how] = $src ? resolveUser($idx, null, $src['username'] ?? null, $src['name'] ?? null, $allowName) : [null, null];
    if ($uid === null) {
        $pmStat['unmatched_users'][] = ($row['user_id'] ?? '?').':'.($src['username'] ?? '-');
        $skipped++;
        continue;
    }
    $pmStat['matched']++;
    $report['match_by'][$how] = ($report['match_by'][$how] ?? 0) + 1;

    $payload = [
        'user_id' => $uid,
        'sender_id' => null,
        'module' => 'pm',
        'type' => $row['type'] ?: 'system',
        'title' => mb_substr((string) $row['title'], 0, 200),
        'content' => $row['content'] ?? null,
        'data' => json_encode(['source' => 'pm:'.$row['id']], JSON_UNESCAPED_UNICODE),
        'related_type' => $row['related_type'] ?? null,
        'related_id' => $row['related_id'] ?? null,
        'priority' => 'normal',
        'is_read' => (int) $row['is_read'],
        'read_at' => $row['read_at'] ?: null,
        'created_at' => $row['created_at'] ?: now(),
        'updated_at' => $row['updated_at'] ?: now(),
    ];

    if ($apply && insertOnce($payload)) {
        $pmStat['inserted']++;
        $inserted++;
    }
}
$report['sources']['pm'] = $pmStat;

// ---------- SOHO ----------
$sq = new PDO('sqlite:E:/_tmp_soho_baseline/database/database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$sohoRows = $sq->query("SELECT * FROM notifications WHERE user_type IN ('admin','user') ORDER BY id")->fetchAll();
$sohoUsers = [];
foreach ($sq->query('SELECT id, name, email FROM users') as $u) {
    $sohoUsers[$u['id']] = $u;
}
$sohoStat = ['total' => count($sohoRows), 'matched' => 0, 'inserted' => 0, 'unmatched_users' => []];
foreach ($sohoRows as $row) {
    $src = $sohoUsers[$row['user_id']] ?? null;
    [$uid, $how] = $src ? resolveUser($idx, $src['email'] ?? null, null, $src['name'] ?? null, $allowName) : [null, null];
    if ($uid === null) {
        $sohoStat['unmatched_users'][] = ($row['user_id'] ?? '?').':'.($src['email'] ?? '-');
        $skipped++;
        continue;
    }
    $sohoStat['matched']++;
    $report['match_by'][$how] = ($report['match_by'][$how] ?? 0) + 1;

    $payload = [
        'user_id' => $uid,
        'module' => 'soho',
        'type' => $row['type'] ?: 'system',
        'title' => mb_substr((string) $row['title'], 0, 200),
        'content' => $row['content'] ?? null,
        'data' => json_encode(array_merge(
            (($d2 = json_decode((string) ($row['data_json'] ?? ''), true)) && is_array($d2) ? $d2 : []),
            ['source' => 'soho:'.$row['id']]
        ), JSON_UNESCAPED_UNICODE),
        'priority' => $row['priority'] ?: 'normal',
        'is_read' => $row['read_at'] ? 1 : 0,
        'read_at' => $row['read_at'] ?: null,
        'tenant_id' => $row['tenant_id'] ?? null,
        'created_at' => $row['created_at'] ?: now(),
        'updated_at' => $row['updated_at'] ?: now(),
    ];

    if ($apply && insertOnce($payload)) {
        $sohoStat['inserted']++;
        $inserted++;
    }
}
$report['sources']['soho'] = $sohoStat;

/** 以 data.source 为幂等键插入；已存在返回 false */
function insertOnce(array $payload): bool
{
    $source = json_decode($payload['data'], true)['source'] ?? null;
    if ($source && Notification::where('data->source', $source)->exists()) {
        return false;
    }
    Notification::create($payload);

    return true;
}

$report['totals'] = [
    'source_rows' => $erpStat['total'] + $pmStat['total'] + $sohoStat['total'],
    'matched' => $erpStat['matched'] + $pmStat['matched'] + $sohoStat['matched'],
    'skipped_unmatched_user' => $skipped,
    'inserted' => $inserted,
];
$report['blocker'] = $report['totals']['matched'] === 0
    ? '合并库 users 里没有与三源账号同 email/username 的记录（用户迁移属 B-05），因此历史通知暂时无主可挂；脚本已就绪，B-05 完成后重跑 --apply 即可。'
    : null;

@mkdir($root.'/docs/notifications', 0777, true);
file_put_contents($root.'/docs/notifications/migration-report.json', json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo ($apply ? '[APPLY] ' : '[DRY-RUN] ')."三源历史通知迁移\n";
foreach (['erp', 'pm', 'soho'] as $m) {
    $s = $report['sources'][$m];
    echo sprintf("  %-4s 源 %3d 条，用户可映射 %3d 条，本次写入 %3d 条\n", strtoupper($m), $s['total'], $s['matched'], $s['inserted']);
}
echo '  合计：源 '.$report['totals']['source_rows'].' 条，可映射 '.$report['totals']['matched'].' 条，写入 '.$report['totals']['inserted']." 条\n";
if ($report['blocker']) {
    echo '  ⚠ '.$report['blocker']."\n";
}
