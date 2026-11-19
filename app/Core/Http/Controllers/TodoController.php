<?php

namespace App\Core\Http\Controllers;

use App\Core\Services\TodoCenterService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 我的待办（P2-2）——四模块聚合入口，只读。
 *
 * 路由：core.todos.index
 */
class TodoController extends Controller
{
    public function __construct(private readonly TodoCenterService $todos)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $groups = $this->todos->groups($user);
        $summary = $this->todos->summary($groups);

        // 模块筛选：全部 / core / erp / pm / soho
        $module = $request->query('module');
        if ($module !== null && ! in_array($module, ['core', 'erp', 'pm', 'soho'], true)) {
            $module = null;
        }

        if ($module !== null) {
            $groups = array_values(array_filter($groups, fn ($g) => $g['module'] === $module));
        }

        // 按时间倒序排列各分组内条目
        foreach ($groups as &$group) {
            usort($group['items'], function ($a, $b) {
                $ta = $a['at']?->timestamp ?? 0;
                $tb = $b['at']?->timestamp ?? 0;

                return $tb <=> $ta;
            });
        }
        unset($group);

        return view('core.todos.index', compact('groups', 'summary', 'module'));
    }
}
