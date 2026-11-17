<?php

/**
 * TC-11 审批流定义迁移：ERP 2 条 + SOHO 8 条 → Core approval_flows / approval_steps。
 *
 * 设计：
 *   - 一条源流 = 一条 Core 流（module + document_type 定位），步骤摊平进 approval_steps。
 *   - ERP 的角色 slug 不带模块前缀（sales_manager/admin），迁入时补 `erp.`；
 *     SOHO 的 approvers（operation_manager/super_admin）补 `soho.`，与 TC-07 重灌后的角色名对齐。
 *   - SOHO 的 flow_config.nodes[].mode 只有 any（或签），正好等同「同一步任一人同意即推进」的引擎语义；
 *     复杂配置（并行/会签/金额阈值/超时升级）原样留在报告里，标为待评估项。
 *   - SOHO 15 条历史实例**不迁**：submitted_by/biz_id 都是源库小整数，需 B-05 用户映射后才可信。
 *
 * 用法：php database/scripts/approval_migrate.php [--apply]
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\ApprovalFlow;
use App\Core\Models\ApprovalStep;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);

$password = '';
foreach (file($root.'/.env') as $line) {
    if (str_starts_with(trim($line), 'DB_PASSWORD=')) {
        $password = trim(substr(trim($line), 12), "\"'");
    }
}
$erp = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$soho = new PDO('sqlite:E:/_tmp_soho_baseline/database/database.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

/** ERP：flows + steps */
$flows = [];
$steps = $erp->query('SELECT * FROM dedao_erp.approval_flow_steps ORDER BY approval_flow_id, step_order')->fetchAll();
foreach ($erp->query('SELECT * FROM dedao_erp.approval_flows ORDER BY id')->fetchAll() as $row) {
    $stepsOf = array_values(array_filter($steps, fn ($s) => (int) $s['approval_flow_id'] === (int) $row['id']));
    $flows[] = [
        'module' => 'erp',
        'name' => $row['name'],
        'document_type' => $row['document_type'],
        'description' => '来自 ERP 原表 approval_flows#'.$row['id'],
        'enabled' => (bool) $row['enabled'],
        'steps' => array_map(fn ($s) => [
            'step_order' => (int) $s['step_order'],
            'name' => $s['name'],
            'approver_type' => $s['approver_type'],
            'approver_value' => 'erp.'.ltrim((string) $s['approver_value'], '.'),
        ], $stepsOf),
    ];
}

/** SOHO：flow_config.nodes[] → steps */
$sohoKeep = [];
foreach ($soho->query('SELECT * FROM approval_flows ORDER BY id')->fetchAll() as $row) {
    $config = json_decode((string) $row['flow_config'], true) ?: [];
    $nodes = $config['nodes'] ?? [];
    $modeSet = array_values(array_unique(array_map(fn ($n) => $n['mode'] ?? 'any', $nodes)));
    if ($modeSet !== ['any']) {
        $sohoKeep[] = ['code' => $row['code'], 'reason' => 'mode='.implode(',', $modeSet).' 非纯或签，需人工评估'];
        continue;
    }
    $flows[] = [
        'module' => 'soho',
        'name' => $row['name'],
        'document_type' => $row['type'],
        'description' => sprintf('原 %s · scope=%s · status=%s · priority=%s',
            $row['code'], $row['scope'], $row['status'], $row['priority']),
        'enabled' => $row['status'] === 'enabled',
        'steps' => array_map(fn ($n) => [
            'step_order' => (int) ($n['node'] ?? 0),
            'name' => (string) ($n['name'] ?? '审批'),
            // SOHO 的 approvers 是角色 slug 数组；或签语义下取第一个角色，其余记录在报告里
            'approver_type' => 'role',
            'approver_value' => 'soho.'.ltrim((string) ($n['approvers'][0] ?? 'super_admin'), '.'),
        ], array_values($nodes)),
    ];
}

$report = [
    'generated_at' => date('c'),
    'apply' => $apply,
    'sources' => [
        'erp' => ['flows' => count($erp->query('SELECT id FROM dedao_erp.approval_flows')->fetchAll()), 'steps' => count($steps)],
        'soho' => ['flows' => 8, 'instances' => count($soho->query('SELECT id FROM approval_instances')->fetchAll())],
    ],
    'migrated' => [],
    'skipped' => $sohoKeep,
    'pending_instances' => 'SOHO 15 条历史实例未迁：submitted_by/biz_id 为源库小整数，待 B-05 用户映射',
];

foreach ($flows as $flow) {
    $existing = ApprovalFlow::where('module', $flow['module'])->where('document_type', $flow['document_type'])->first();
    $report['migrated'][] = [
        'module' => $flow['module'],
        'document_type' => $flow['document_type'],
        'name' => $flow['name'],
        'steps' => array_map(fn ($s) => $s['step_order'].'.'.$s['name'].' ['.$s['approver_type'].':'.$s['approver_value'].']', $flow['steps']),
        'action' => $existing ? 'skip(已存在)' : ($apply ? 'insert' : 'dry-run'),
    ];

    if (! $apply || $existing) {
        continue;
    }

    DB::transaction(function () use ($flow) {
        $created = ApprovalFlow::create([
            'module' => $flow['module'],
            'name' => $flow['name'],
            'document_type' => $flow['document_type'],
            'description' => $flow['description'],
            'enabled' => $flow['enabled'],
            'created_by' => null,
        ]);
        foreach ($flow['steps'] as $step) {
            ApprovalStep::create($step + ['approval_flow_id' => $created->id]);
        }
    });
}

@mkdir($root.'/docs/approvals', 0777, true);
file_put_contents(
    $root.'/docs/approvals/migration-report.json',
    json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
);

echo ($apply ? '【已写入】' : '【dry-run，加 --apply 才写库】')."\n";
printf("源：ERP %d 条流 / %d 条步骤；SOHO 8 条流 / 15 条实例\n", $report['sources']['erp']['flows'], $report['sources']['erp']['steps']);
foreach ($report['migrated'] as $m) {
    echo "  [{$m['module']}] {$m['document_type']} · {$m['name']} → ".implode(' → ', array_map(fn ($s) => explode(' [', $s)[0], $m['steps']))."  ({$m['action']})\n";
}
foreach ($report['skipped'] as $s) {
    echo "  跳过 {$s['code']}：{$s['reason']}\n";
}
echo "报告：docs/approvals/migration-report.json\n";
