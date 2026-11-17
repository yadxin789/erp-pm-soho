<?php
/**
 * TC-08 菜单基线抽取（行式解析，避免大正则回溯问题）。
 *
 * 输出 docs/menu/baseline-menu.json
 * 只读源文件，不改参考项目。
 */

$root = dirname(__DIR__, 2);
$out = [];

// ---------------- ERP：逐行扫描 sidebar_main.blade.php ----------------
$lines = file('D:/laravel12-erp/resources/views/partials/sidebar_main.blade.php', FILE_IGNORE_NEW_LINES);

$groups = [];
$currentGroup = '未分组';
$erpCount = 0;
$pendingRoute = null;

foreach ($lines as $i => $line) {
    // 分组标题
    if (preg_match('/<div class="\{\{ \$section \}\}[^"]*">\s*(.+?)\s*<\/div>/u', $line, $mm)) {
        $currentGroup = trim($mm[1]);
        continue;
    }
    // 权限判定行，记住 route 名
    if (preg_match("/Menu::can\('([^']+)'\)/", $line, $mm)) {
        $pendingRoute = $mm[1];
        continue;
    }
    // 菜单链接行
    if ($pendingRoute !== null && preg_match('/<a href="\{\{ route\(/', $line)) {
        $linkLine = $line;
        $icon = '';
        $label = '';

        // 同一行可能已含 <i> 和文字，也可能在下一行
        if (preg_match('/<i class="([^"]+)"><\/i>(.*?)<\/a>/u', $linkLine, $mm)) {
            $icon = $mm[1];
            $label = trim($mm[2]);
        } else {
            // 向下找 icon 行 / 文本行
            for ($j = $i + 1; $j < min($i + 4, count($lines)); $j++) {
                if (preg_match('/<i class="([^"]+)"><\/i>(.*)$/u', $lines[$j], $mm)) {
                    $icon = $mm[1];
                    $label = trim($mm[2]);
                    break;
                }
                if (preg_match('/^([^<<]+)$/u', trim($lines[$j]), $mm)) {
                    $label = trim($mm[1]);
                }
            }
        }

        $label = trim(preg_replace('/\s+/u', '', strip_tags($label)));
        $groups[$currentGroup][] = ['route' => $pendingRoute, 'label' => $label, 'icon' => $icon];
        $erpCount++;
        $pendingRoute = null;
    }
}

$out['erp'] = ['groups' => $groups, 'count' => $erpCount];

// ---------------- PM ----------------
$pmSrc = file_get_contents('E:/my-pm/pm-server-openEuler/resources/views/layouts/app.blade.php');

preg_match_all('/@if\((.*?)\)/s', $pmSrc, $cond, PREG_OFFSET_CAPTURE);
preg_match_all(
    '/<a class="nav-link[^"]*"\s+href="\{\{ route\(\'([^\']+)\'\) \}\}"[^>]*>(.*?)<\/a>/su',
    $pmSrc,
    $pmLinks,
    PREG_OFFSET_CAPTURE
);

$pmItems = [];
foreach ($pmLinks[1] as $i => $r) {
    $route = trim($r[0]);
    $pos = $pmLinks[0][$i][1];
    $inner = $pmLinks[2][$i][0];

    $label = trim(preg_replace('/\s+/u', '', strip_tags($inner)));
    preg_match('/<i class="([^"]+)"><\/i>/', $inner, $iconMatch);
    $icon = $iconMatch[1] ?? '';

    $scope = '';
    foreach ($cond[1] as $j => $c) {
        if ($cond[0][$j][1] < $pos) {
            $scope = trim(preg_replace('/\s+/u', '', $c[0]));
        }
    }

    if (! isset($pmItems[$route])) {
        $pmItems[$route] = ['route' => $route, 'label' => $label, 'icon' => $icon, 'scopes' => []];
    }
    if ($scope !== '' && ! in_array($scope, $pmItems[$route]['scopes'], true)) {
        $pmItems[$route]['scopes'][] = $scope;
    }
}

$out['pm'] = ['items' => array_values($pmItems), 'count' => count($pmItems), 'raw_links' => count($pmLinks[1])];

// ---------------- SOHO ----------------
require 'E:/ddwl-soho/app/Support/MenuConfig.php';
$sohoMenus = App\Support\MenuConfig::getMenus();
$sohoItems = [];
foreach ($sohoMenus as $key => $m) {
    $sohoItems[] = [
        'key'        => $key,
        'label'      => $m['title'] ?? $key,
        'route'      => $m['route'] ?? '',
        'icon'       => $m['icon'] ?? '',
        'permission' => $m['permission'] ?? null,
        'roles'      => $m['roles'] ?? [],
        'order'      => $m['order'] ?? 0,
    ];
}
$out['soho'] = ['items' => $sohoItems, 'count' => count($sohoItems)];

$dir = $root.'/docs/menu';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}
file_put_contents($dir.'/baseline-menu.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "已导出 docs/menu/baseline-menu.json\n";
echo "  ERP : {$erpCount} 项 / ".count($groups)." 组\n";
echo '  PM  : '.count($pmItems).' 项（原始链接 '.count($pmLinks[1])." 条，含按角色重复）\n";
echo '  SOHO: '.count($sohoItems)." 项\n";
