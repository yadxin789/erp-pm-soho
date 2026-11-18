<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PermissionChangeLog;
use Illuminate\Http\Request;

/**
 * 权限变更日志控制器
 * 展示用户权限变更历史
 */
class PermissionChangeLogController extends Controller
{
    /**
     * 显示权限变更日志列表
     */
    public function index(Request $request)
    {
        $query = PermissionChangeLog::query()
            ->with(['targetUser:id,name,email', 'operator:id,name', 'role:id,name', 'permission:id,name']);

        // 变更类型筛选
        if ($type = $request->input('type')) {
            $query->where('change_type', $type);
        }

        // 目标用户筛选
        if ($userId = $request->input('user_id')) {
            $query->where('target_user_id', $userId);
        }

        // 操作人筛选
        if ($operatorId = $request->input('operator_id')) {
            $query->where('operator_id', $operatorId);
        }

        // 时间范围
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 一次性获取全部记录
        $logs = $query->orderByDesc('created_at')->get();

        $changeTypeLabels = PermissionChangeLog::getChangeTypeLabels();
        $changeTypeColors = PermissionChangeLog::getChangeTypeColors();

        return view('security.permission-change-logs.index', compact('logs', 'changeTypeLabels', 'changeTypeColors'));
    }

    /**
     * 显示单条日志详情
     */
    public function show(PermissionChangeLog $log)
    {
        $log->load(['targetUser', 'operator', 'role', 'permission']);

        return view('security.permission-change-logs.show', compact('log'));
    }
}