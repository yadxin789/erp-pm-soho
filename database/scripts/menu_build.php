<?php
/**
 * TC-08 统一菜单构建：docs/menu/baseline-menu.json → resources/menu/{erp,pm,soho}.php
 *
 * 归一化规则：
 *   路由名：erp. / pm. / soho. + 原路由名（ERP 原 `app.xxx` 去掉 `app.` 再加 `erp.`）
 *   权限名：ERP 原系统没有 permission 中间件，可见性由 RoleScope「角色→业务模块」决定，因此存 roles；
 *           PM / SOHO 用 RBAC 库里的真实权限名，逐条校验存在性
 *
 * 用法：php database/scripts/menu_build.php
 */

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$baseline = json_decode(file_get_contents($root.'/docs/menu/baseline-menu.json'), true);
$existing = array_flip(DB::table('permissions')->pluck('name')->all());
$unresolved = [];

function cleanLabel(string $label, string $fallback = ''): string
{
    $label = preg_replace('/@php.*?@endphp/su', '', $label);
    $label = preg_replace('/@if\(.*?\)(.*?)@endif/su', '$1', $label);
    $label = preg_replace('/\{\{.*?\}\}/su', '', $label);
    $label = strip_tags($label);
    $label = trim(preg_replace('/\s+/u', '', $label));

    return $label !== '' ? $label : $fallback;
}

function normIcon(string $icon): string
{
    $icon = trim($icon);
    if ($icon === '') {
        return 'fa-solid fa-circle-dot';
    }
    $parts = array_values(array_filter(preg_split('/\s+/', $icon), fn ($p) => $p !== 'me-2' && $p !== 'fas' && $p !== 'fa'));
    $name = end($parts) ?: 'fa-circle-dot';
    if (strpos($name, 'fa-') !== 0) {
        $name = 'fa-'.$name;
    }

    return 'fa-solid '.$name;
}

// ============ ERP ============
$ERP_SHARED = ['dashboard', 'ai', 'chat', 'approvals', 'reports', 'masterdata'];
$ERP_PM_MODULES = ['projects', 'tasks', 'messages', 'collab', 'voice', 'files', 'todos', 'extensions', 'knowledge', 'project-templates', 'support', 'pm-dashboard', 'recycle'];
$ERP_SCOPES = [
    'admin' => ['admin'],
    'sales_manager' => ['sales', 'crm', 'finance'],
    'purchase_manager' => ['purchase', 'finance'],
    'production_manager' => ['production', 'inventory'],
    'warehouse_manager' => ['inventory', 'purchase', 'production'],
    'quality_manager' => ['quality', 'purchase', 'production'],
    'accountant' => ['finance', 'sales', 'purchase', 'production', 'inventory', 'quality'],
    'viewer' => ['sales', 'purchase', 'production', 'inventory', 'quality', 'hr', 'projects', 'assets', 'ecommerce'],
    'hr_specialist' => ['hr'],
    'project_manager' => ['projects'],
    'asset_manager' => ['assets'],
    'ecommerce_operator' => ['ecommerce'],
];
$ERP_ROLES = array_keys($ERP_SCOPES);

function erpModuleOf(string $oldRoute): string
{
    if (! str_starts_with($oldRoute, 'app.')) {
        return 'PUBLIC';
    }
    $seg = explode('.', $oldRoute);
    $module = $seg[1] ?? 'PUBLIC';

    return $module === 'profile' ? 'PUBLIC' : $module;
}

function erpRoleAllows(string $role, string $module, array $SHARED, array $PM_MODULES, array $SCOPES): bool
{
    if ($role === 'admin') {
        return $module === 'admin';
    }
    if (in_array($module, $PM_MODULES, true)) {
        $module = 'projects';
    }

    return in_array($module, $SHARED, true) || in_array($module, $SCOPES[$role] ?? [], true);
}

$built = ['erp' => [], 'pm' => [], 'soho' => []];

$order = 0;
foreach ($baseline['erp']['groups'] as $groupName => $items) {
    foreach ($items as $item) {
        $order++;
        $old = $item['route'];
        $route = 'erp.'.preg_replace('/^app\./', '', $old);
        $module = erpModuleOf($old);

        $roles = null;
        $note = null;
        if ($module !== 'PUBLIC') {
            if (in_array($module, $ERP_PM_MODULES, true)) {
                $roles = array_values(array_diff($ERP_ROLES, ['admin']));
                $note = '参与项目校验待 ERP 迁入时接入';
            } else {
                $roles = [];
                foreach ($ERP_ROLES as $r) {
                    if (erpRoleAllows($r, $module, $ERP_SHARED, $ERP_PM_MODULES, $ERP_SCOPES)) {
                        $roles[] = 'erp.'.$r;
                    }
                }
            }
        }

        $built['erp'][] = [
            'key'        => 'erp.'.$old,
            'group'      => $groupName,
            'label'      => cleanLabel($item['label'] ?? '', $old),
            'icon'       => normIcon($item['icon'] ?? ''),
            'route'      => $route,
            'permission' => null,
            'roles'      => $roles,
            'order'      => $order,
            'source'     => $old,
            'note'       => $note,
        ];
    }
}

// ============ PM ============
$PM_PERM = [
    'dashboard' => 'pm.dashboard.view',
    'ai.settings' => 'pm.ai.settings',
    'ai.chat' => 'pm.ai.use',
    'ai-delegation.index' => 'pm.ai.delegation',
    'admin.users' => 'pm.admin.users',
    'admin.departments' => 'pm.admin.departments',
    'admin.roles' => 'pm.admin.roles',
    'admin.statuses' => 'pm.admin.statuses',
    'admin.task-types' => 'pm.admin.task_types',
    'admin.chat-messages' => 'pm.chat.view',
    'admin.file-repository' => 'pm.file.view',
    'admin.trash' => 'pm.trash.manage',
    'knowledge.index' => 'pm.kb.view',
    'admin.operation-logs' => 'pm.log.view',
    'admin.send-notification' => 'pm.notice.send',
    'admin.services' => 'pm.admin.services',
    'admin.wecom.settings' => 'pm.admin.settings',
    'project-template.index' => 'pm.project.view',
    'project.create' => 'pm.project.create',
    'project.my-projects' => 'pm.project.view',
    'extension.index' => 'pm.project.audit',
    'file.resource-files' => 'pm.file.view',
    'notification.index' => 'pm.notice.view',
    'profile.wecom.index' => null,
    'wecom.file.index' => 'pm.file.view',
    'project.index' => 'pm.project.view',
    'task.my-audit-tasks.pending-audit' => 'pm.task.approve',
    'task.my-tasks.pending-execution' => 'pm.task.view',
    'support.index' => 'pm.task.view',
    'file.manage-resource-files' => 'pm.file.view',
    'my-trash' => 'pm.trash.view',
];

$PM_GROUP = [
    'dashboard' => '工作台',
    'ai.settings' => '智能助手', 'ai.chat' => '智能助手', 'ai-delegation.index' => '智能助手',
    'admin.users' => '系统管理', 'admin.departments' => '系统管理', 'admin.roles' => '系统管理',
    'admin.statuses' => '系统管理', 'admin.task-types' => '系统管理', 'admin.chat-messages' => '系统管理',
    'admin.trash' => '系统管理', 'admin.operation-logs' => '系统管理', 'admin.services' => '系统管理',
    'admin.wecom.settings' => '系统管理',
    'project-template.index' => '项目', 'project.create' => '项目', 'project.my-projects' => '项目', 'project.index' => '项目',
    'extension.index' => '项目',
    'task.my-audit-tasks.pending-audit' => '任务', 'task.my-tasks.pending-execution' => '任务',
    'knowledge.index' => '文件与知识', 'file.resource-files' => '文件与知识', 'file.manage-resource-files' => '文件与知识',
    'wecom.file.index' => '文件与知识', 'admin.file-repository' => '文件与知识', 'support.index' => '文件与知识',
    'notification.index' => '通知', 'admin.send-notification' => '通知',
    'profile.wecom.index' => '个人',
];

$PM_LABEL_FIX = ['my-trash' => '我的回收站', 'ai-delegation.index' => 'AI 授权中心'];

$order = 0;
foreach ($baseline['pm']['items'] as $item) {
    $order++;
    $old = $item['route'];
    $route = 'pm.'.$old;

    $permission = null;
    if (array_key_exists($old, $PM_PERM)) {
        $permission = $PM_PERM[$old];
        if ($permission !== null && ! isset($existing[$permission])) {
            $unresolved[] = ['module' => 'pm', 'route' => $route, 'label' => $item['label'], 'tried' => [$permission]];
            $permission = null;
        }
    } else {
        $unresolved[] = ['module' => 'pm', 'route' => $route, 'label' => $item['label'], 'tried' => ['(无映射)']];
    }

    $built['pm'][] = [
        'key'        => 'pm.'.$old,
        'group'      => $PM_GROUP[$old] ?? '其他',
        'label'      => $PM_LABEL_FIX[$old] ?? cleanLabel($item['label'], $old),
        'icon'       => normIcon($item['icon'] ?? ''),
        'route'      => $route,
        'permission' => $permission,
        'roles'      => null,
        'order'      => $order,
        'source'     => $old,
    ];
}

// ============ SOHO ============
function sohoGroup(int $order): string
{
    return match (true) {
        $order < 20 => '工作台与报表',
        $order < 50 => '系统与平台',
        $order < 70 => '商品',
        $order < 90 => '订单与客户',
        $order < 100 => '营销',
        $order < 110 => '库存与仓储',
        $order < 130 => '履约与物流',
        $order < 140 => '售后',
        $order < 150 => '财务',
        $order < 160 => '采购',
        $order < 190 => '集成与运维',
        $order < 200 => '审计',
        default => '经营分析',
    };
}

/**
 * SOHO 菜单里有 19 条引用了源权限表里并不存在的权限名（MenuConfig 早于 RBAC 表成型）。
 * 按语义就近映射到真实权限，映射明细写进 build-report，不静默丢掉闸门。
 */
$SOHO_PERM_ALIAS = [
    'pick.task.view' => ['index' => 'picking.view', 'progress' => 'picking.view', 'completed' => 'picking.view',
        'exception' => 'picking.view', 'scan' => 'picking.execute'],
    'check.task.view' => ['index' => 'checking.view', 'exception' => 'checking.view', 'completed' => 'checking.view',
        'record' => 'checking.view', 'scan' => 'checking.execute'],
    'pack.task.view' => ['index' => 'packing.view', 'completed' => 'packing.view', 'exception' => 'packing.view',
        'scan' => 'packing.execute', 'material' => 'packing.execute', 'weight' => 'packing.execute',
        'parcel' => 'packing.execute'],
    'shipment.view' => ['pending' => 'logistics.view'],
    'procurement.approval' => ['index' => 'procurement.order.approve'],
];

$aliased = [];

foreach ($baseline['soho']['items'] as $item) {
    $rawPerm = $item['permission'];
    $permission = $rawPerm ? 'soho.'.$rawPerm : null;

    if ($permission !== null && ! isset($existing[$permission]) && isset($SOHO_PERM_ALIAS[$rawPerm])) {
        $leaf = explode('.', (string) $item['route']);
        $leaf = end($leaf);
        $map = $SOHO_PERM_ALIAS[$rawPerm];
        if (isset($map[$leaf])) {
            $aliased[] = ['route' => 'soho.'.$item['route'], 'from' => $permission, 'to' => 'soho.'.$map[$leaf]];
            $permission = 'soho.'.$map[$leaf];
        }
    }

    if ($permission !== null && ! isset($existing[$permission])) {
        $unresolved[] = ['module' => 'soho', 'route' => 'soho.'.$item['route'], 'label' => $item['label'], 'tried' => [$permission]];
        $permission = null;
    }

    $roles = null;
    if (! empty($item['roles'])) {
        $roles = array_map(fn ($r) => 'soho.'.$r, $item['roles']);
    }

    $built['soho'][] = [
        'key'        => 'soho.'.$item['key'],
        'group'      => sohoGroup((int) $item['order']),
        'label'      => cleanLabel($item['label'], $item['key']),
        'icon'       => normIcon($item['icon'] ?? ''),
        'route'      => 'soho.'.$item['route'],
        'permission' => $permission,
        'roles'      => $roles,
        'order'      => (int) $item['order'],
        'source'     => $item['route'],
    ];
}

// ============ 写文件 ============
// 补齐 TC-08 约定的字段：module / sort / children（结构对齐 module/group/label/icon/route/permission/children/sort）
foreach ($built as $module => &$items) {
    foreach ($items as &$it) {
        $it['module'] = $module;
        $it['sort'] = $it['order'];
        $it['children'] = [];
    }
    unset($it);
}
unset($items);

$dir = $root.'/resources/menu';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($built as $module => $items) {
    $php = "<?php\n\n/*\n| ".strtoupper($module)." 模块菜单（TC-08 由 docs/menu/baseline-menu.json 生成，勿手改）\n| key / group / label / icon / route / permission / roles / order / source\n| route 为合并后真实路由名；permission 已对照 RBAC 库校验；roles 为模块内角色白名单（null=不限）\n*/\n\nreturn "
        .var_export($items, true)
        .";\n";
    file_put_contents($dir.'/'.$module.'.php', $php);
}

file_put_contents($root.'/docs/menu/build-report.json', json_encode([
    'generated_at' => date('Y-m-d H:i:s'),
    'counts' => ['erp' => count($built['erp']), 'pm' => count($built['pm']), 'soho' => count($built['soho'])],
    'unresolved_permissions' => $unresolved,
    'permission_aliases' => $aliased,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "menu 文件已生成：resources/menu/{erp,pm,soho}.php\n";
echo '  条数 erp='.count($built['erp']).' pm='.count($built['pm']).' soho='.count($built['soho'])."\n";
echo '  权限未解析：'.count($unresolved)."\n";
echo '  权限别名映射：'.count($aliased)."（源权限表缺名，已就近映射）\n";
foreach (array_slice($unresolved, 0, 20) as $u) {
    echo '    ['.$u['module'].'] '.$u['label'].' ('.$u['route'].') 试过 '.implode(' / ', $u['tried'])."\n";
}
