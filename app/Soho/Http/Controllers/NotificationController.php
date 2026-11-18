<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * 通知列表
     * 获取当前用户的所有通知，支持类型筛选和已读状态筛选
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Notification::query()
            ->where('user_type', get_class($user))
            ->where('user_id', $user->id);

        // 类型筛选
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 已读状态筛选
        if ($request->filled('is_read')) {
            if ($request->is_read == '0') {
                $query->whereNull('read_at');
            } else {
                $query->whereNotNull('read_at');
            }
        }

        // 搜索
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $notifications = $query->orderByRaw('read_at IS NULL DESC, created_at DESC')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => Notification::where('user_type', get_class($user))
                ->where('user_id', $user->id)
                ->count(),
            'unread' => Notification::where('user_type', get_class($user))
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'system' => Notification::where('user_type', get_class($user))
                ->where('user_id', $user->id)
                ->where('type', 'system')
                ->count(),
            'order' => Notification::where('user_type', get_class($user))
                ->where('user_id', $user->id)
                ->where('type', 'order')
                ->count(),
        ];

        $unreadCount = $stats['unread'];

        return view('notifications.index', compact('notifications', 'unreadCount', 'stats'));
    }

    /**
     * 标记单条通知为已读
     */
    public function markAsRead(Notification $notification)
    {
        // 权限检查：确保通知属于当前用户
        $user = auth()->user();
        if ($notification->user_type !== get_class($user) || $notification->user_id !== $user->id) {
            abort(403, '无权操作此通知');
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => '通知已标记为已读',
        ]);
    }

    /**
     * 标记所有通知为已读
     */
    public function markAllAsRead()
    {
        $user = auth()->user();

        Notification::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => '所有通知已标记为已读',
        ]);
    }

    /**
     * 删除已读通知
     */
    public function deleteRead()
    {
        $user = auth()->user();

        Notification::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->delete();

        return redirect()->route('notifications.index')->with('success', '已删除所有已读通知');
    }

    /**
     * 批量操作
     */
    public function batchAction(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return redirect()->back()->with('error', '请先选择要操作的通知');
        }

        $query = Notification::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->whereIn('id', $ids);

        if ($action === 'read') {
            $query->update(['read_at' => now()]);
            $message = '已将选中通知标记为已读';
        } elseif ($action === 'delete') {
            $query->delete();
            $message = '已删除选中通知';
        } else {
            return redirect()->back()->with('error', '无效的操作');
        }

        return redirect()->route('notifications.index')->with('success', $message);
    }

    /**
     * 删除通知
     */
    public function destroy(Notification $notification)
    {
        // 权限检查：确保通知属于当前用户
        $user = auth()->user();
        if ($notification->user_type !== get_class($user) || $notification->user_id !== $user->id) {
            abort(403, '无权操作此通知');
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => '通知已删除',
        ]);
    }

    /**
     * 获取未读通知数量（用于顶部角标显示）
     */
    public function unreadCount()
    {
        $user = auth()->user();

        $count = Notification::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}