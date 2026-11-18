<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 登录日志控制器
 * 展示用户登录历史，支持按用户/IP/时间筛选
 */
class LoginLogController extends Controller
{
    /**
     * 显示登录日志列表
     */
    public function index(Request $request)
    {
        $query = LoginLog::query()->with('user:id,name,email');

        // 关键词搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // 用户筛选
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // 状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // IP筛选
        if ($ip = $request->input('ip')) {
            $query->where('ip_address', $ip);
        }

        // 时间范围
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom) {
            $query->where('login_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('login_at', '<=', $dateTo . ' 23:59:59');
        }

        // 一次性获取全部记录
        $logs = $query->orderByDesc('login_at')->get();

        // 统计数据
        $stats = $this->getStatistics();

        $statusLabels = LoginLog::getStatusLabels();
        $statusColors = LoginLog::getStatusColors();
        $loginTypeLabels = LoginLog::getLoginTypeLabels();
        $deviceTypeLabels = LoginLog::getDeviceTypeLabels();

        return view('security.login-logs.index', compact(
            'logs', 'stats', 'statusLabels', 'statusColors', 'loginTypeLabels', 'deviceTypeLabels'
        ));
    }

    /**
     * 显示单条日志详情
     */
    public function show(LoginLog $log)
    {
        $log->load('user');

        // 同一IP的其他登录记录
        $relatedLogs = LoginLog::where('ip_address', $log->ip_address)
            ->where('id', '!=', $log->id)
            ->orderByDesc('login_at')
            ->limit(10)
            ->get();

        return view('security.login-logs.show', compact('log', 'relatedLogs'));
    }

    /**
     * 获取统计数据
     */
    private function getStatistics(): array
    {
        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7);

        return [
            'total' => LoginLog::count(),
            'success' => LoginLog::success()->count(),
            'failed' => LoginLog::failed()->count(),
            'today' => LoginLog::where('login_at', '>=', $today)->count(),
            'week' => LoginLog::where('login_at', '>=', $weekAgo)->count(),
            'unique_users' => LoginLog::success()
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),
            'unique_ips' => LoginLog::success()
                ->distinct('ip_address')
                ->count('ip_address'),
        ];
    }
}