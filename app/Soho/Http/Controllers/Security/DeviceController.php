<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * 设备管理控制器
 * 按设备维度聚合展示会话，管理用户登录设备
 */
class DeviceController extends Controller
{
    /**
     * 显示设备列表（按设备聚合）
     */
    public function index(Request $request)
    {
        // 从 sessions 表读取并按设备分组
        $devices = $this->getDevicesByAggregate();

        // 统计
        $stats = [
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('is_online', true)->count(),
            'users_count' => $devices->pluck('user_id')->unique()->filter()->count(),
        ];

        return view('security.devices.index', compact('devices', 'stats'));
    }

    /**
     * 强制下线指定设备
     */
    public function destroy(Request $request, string $sessionId)
    {
        if ($sessionId === Session::getId()) {
            return back()->with('error', '不能下线当前设备');
        }

        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();

        if ($deleted) {
            return back()->with('success', '设备已下线');
        }

        return back()->with('error', '设备不存在或已离线');
    }

    /**
     * 按设备聚合获取会话
     */
    private function getDevicesByAggregate(): \Illuminate\Support\Collection
    {
        // 直接获取所有会话，前端按设备分组显示
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $session->browser = $this->parseBrowser($session->user_agent);
                $session->os = $this->parseOS($session->user_agent);
                $session->device_name = $this->buildDeviceName($session);
                $session->last_activity_at = now()->setTimestamp($session->last_activity);
                $session->is_online = $session->last_activity_at->gt(now()->subMinutes(15));
                $session->is_current = $session->id === Session::getId();
                return $session;
            });

        return $sessions;
    }

    /**
     * 构建设备名称
     */
    private function buildDeviceName($session): string
    {
        $os = $this->parseOS($session->user_agent);
        $browser = $this->parseBrowser($session->user_agent);
        return "{$os} - {$browser}";
    }

    /**
     * 解析浏览器
     */
    private function parseBrowser(?string $userAgent): string
    {
        if (!$userAgent) return '未知';
        if (str_contains($userAgent, 'Edg/')) return 'Edge';
        if (str_contains($userAgent, 'Chrome/')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox/')) return 'Firefox';
        if (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) return 'Safari';
        if (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/')) return 'IE';
        return '其他';
    }

    /**
     * 解析操作系统
     */
    private function parseOS(?string $userAgent): string
    {
        if (!$userAgent) return '未知';
        if (str_contains($userAgent, 'Windows NT 10')) return 'Windows 10';
        if (str_contains($userAgent, 'Windows NT 6')) return 'Windows';
        if (str_contains($userAgent, 'Mac OS X')) return 'macOS';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        return '其他';
    }
}