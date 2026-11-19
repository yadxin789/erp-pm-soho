<?php

namespace App\Soho\Http\Controllers;

use App\Core\Models\Notification;
use Illuminate\Http\Request;

/**
 * SOHO 通知消息列表（P2-2 统一）。
 *
 * 数据源统一到 Core `notifications`（TC-09 三源并集），不再读写 `soho_notifications`。
 * 页面 URL（`/soho/notifications`）、视图、JSON 契约保持不变，只把底层表换成 Core 一份，
 * 模块过滤取 `module in (soho, core)`，因此 SOHO 侧也能看到站内全局通知。
 */
class NotificationController extends Controller
{
    /** 当前模块可见的通知来源 */
    private const VISIBLE_MODULES = ['soho', 'core'];

    private function baseQuery()
    {
        $user = auth()->user();

        return Notification::query()
            ->where('user_id', $user->id)
            ->whereIn('module', self::VISIBLE_MODULES);
    }

    /**
     * 通知列表：支持类型筛选、已读状态筛选、关键词搜索。
     */
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_read')) {
            if ($request->is_read == '0') {
                $query->where('is_read', false);
            } else {
                $query->where('is_read', true);
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $notifications = $query->orderByRaw('is_read ASC, created_at DESC')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => $this->baseQuery()->count(),
            'unread' => $this->baseQuery()->where('is_read', false)->count(),
            'system' => $this->baseQuery()->where('type', 'system')->count(),
            'order' => $this->baseQuery()->where('type', 'order')->count(),
        ];

        $unreadCount = $stats['unread'];

        return view('notifications.index', compact('notifications', 'unreadCount', 'stats'));
    }

    /**
     * 未读数（顶栏角标轮询）。
     */
    public function unreadCount()
    {
        return response()->json(['count' => $this->baseQuery()->where('is_read', false)->count()]);
    }

    /**
     * 标记单条已读。
     */
    public function markAsRead(Notification $notification)
    {
        $user = auth()->user();
        if ((int) $notification->user_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => '无权操作此通知'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => '通知已标记为已读']);
    }

    /**
     * 全部标记已读。
     */
    public function markAllAsRead()
    {
        $this->baseQuery()->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true, 'message' => '所有通知已标记为已读']);
    }

    /**
     * 删除全部已读。
     */
    public function deleteRead()
    {
        $this->baseQuery()->where('is_read', true)->delete();

        return redirect()->route('soho.notifications.index')->with('success', '已删除所有已读通知');
    }

    /**
     * 批量操作：标记已读 / 删除。
     */
    public function batchAction(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        $action = $request->input('action');

        $query = $this->baseQuery()->whereIn('id', $ids);

        if ($action === 'read') {
            $query->update(['is_read' => true, 'read_at' => now()]);
            $message = '已标记所选通知为已读';
        } elseif ($action === 'delete') {
            $query->delete();
            $message = '已删除所选通知';
        } else {
            return response()->json(['success' => false, 'message' => '未知操作'], 422);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * 删除单条通知。
     */
    public function destroy(Notification $notification)
    {
        $user = auth()->user();
        if ((int) $notification->user_id !== (int) $user->id) {
            return redirect()->route('soho.notifications.index')->with('error', '无权操作此通知');
        }

        $notification->delete();

        return redirect()->route('soho.notifications.index')->with('success', '通知已删除');
    }
}
