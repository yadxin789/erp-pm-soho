<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * 会话管理控制器
 * 展示活跃会话列表，支持强制下线操作
 */
class SessionController extends Controller
{
    /**
     * 显示会话列表
     */
    public function index(Request $request)
    {
        // 从 sessions 表读取活跃会话
        $sessions = $this->getActiveSessions();

        // 统计
        $stats = [
            'total' => $sessions->count(),
            'users' => $sessions->pluck('user_id')->unique()->filter()->count(),
            'guests' => $sessions->whereNull('user_id')->count(),
            'current' => $sessions->where('id', Session::getId())->count(),
        ];

        return view('security.sessions.index', compact('sessions', 'stats'));
    }

    /**
     * 强制下线指定会话
     */
    public function destroy(Request $request, string $sessionId)
    {
        // 不允许下线自己的会话
        if ($sessionId === Session::getId()) {
            return back()->with('error', '不能下线当前会话');
        }

        // 删除会话记录
        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();

        if ($deleted) {
            return back()->with('success', '会话已强制下线');
        }

        return back()->with('error', '会话不存在或已过期');
    }

    /**
     * 批量下线会话
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'string',
        ]);

        $currentSessionId = Session::getId();
        $sessionIds = array_filter($request->input('session_ids'), function ($id) use ($currentSessionId) {
            return $id !== $currentSessionId;
        });

        if (empty($sessionIds)) {
            return back()->with('warning', '未选择可下线的会话');
        }

        $deleted = DB::table('sessions')->whereIn('id', $sessionIds)->delete();

        return back()->with('success', "已下线 {$deleted} 个会话");
    }

    /**
     * 获取活跃会话列表
     */
    private function getActiveSessions(): \Illuminate\Support\Collection
    {
        // 会话表结构：id, user_id, ip_address, user_agent, payload, last_activity
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
                // 解析 User-Agent
                $session->browser = $this->parseBrowser($session->user_agent);
                $session->os = $this->parseOS($session->user_agent);
                $session->last_activity_at = now()->setTimestamp($session->last_activity);
                $session->is_current = $session->id === Session::getId();
                return $session;
            });

        return $sessions;
    }

    /**
     * 解析浏览器信息
     */
    private function parseBrowser(?string $userAgent): string
    {
        if (!$userAgent) {
            return '未知';
        }

        if (str_contains($userAgent, 'Edg/')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'Chrome/') && !str_contains($userAgent, 'Edg/')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Firefox/')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/')) {
            return 'IE';
        }

        return '其他';
    }

    /**
     * 解析操作系统信息
     */
    private function parseOS(?string $userAgent): string
    {
        if (!$userAgent) {
            return '未知';
        }

        if (str_contains($userAgent, 'Windows NT 10')) {
            return 'Windows 10';
        }
        if (str_contains($userAgent, 'Windows NT 6.3')) {
            return 'Windows 8.1';
        }
        if (str_contains($userAgent, 'Windows NT 6.1')) {
            return 'Windows 7';
        }
        if (str_contains($userAgent, 'Mac OS X')) {
            return 'macOS';
        }
        if (str_contains($userAgent, 'Linux')) {
            if (str_contains($userAgent, 'Android')) {
                return 'Android';
            }
            return 'Linux';
        }
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }

        return '其他';
    }
}