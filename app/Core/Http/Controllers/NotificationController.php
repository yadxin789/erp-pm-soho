<?php

namespace App\Core\Http\Controllers;

use App\Core\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 站内通知中心（TC-09）——erp/pm/soho 三套通知合并到 Core 一份。
 *
 * 路由：core.notifications.index / .read / .read-all / .destroy / .unread-count
 * 模块内的通知入口（`erp.notifications.index` 等）迁入时应改为跳 Core 路由。
 */
class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $module = $request->query('module');
        if ($module !== null && ! in_array($module, NotificationService::MODULES, true)) {
            $module = null;
        }

        $status = $request->query('status');           // unread / read / null
        $unreadOnly = match ($status) {
            'unread' => true,
            'read' => false,
            default => null,
        };

        $list = $this->notifications
            ->queryFor($user, $module, $unreadOnly)
            ->paginate(15)
            ->withQueryString();

        return view('core::notifications.index', [
            'items' => $list,
            'module' => $module,
            'status' => $status,
            'counts' => $this->notifications->unreadByModule($user),
            'byModule' => $this->notifications->totalByModule($user),
            'totalCount' => $list->total(),
        ]);
    }

    public function read(Request $request, int $id)
    {
        $notification = $this->notifications->markRead($request->user(), $id);
        abort_if($notification === null, 404, '通知不存在或不属于当前账号。');

        $target = $notification->link;
        if (is_string($target) && $target !== '') {
            // 只允许站内相对地址，避免被 data 里的外链带跑
            return redirect()->to(str_starts_with($target, 'http') ? url('/notifications') : $target);
        }

        return redirect()->route('core.notifications.index');
    }

    public function readAll(Request $request)
    {
        $module = $request->input('module');
        if ($module !== null && ! in_array($module, NotificationService::MODULES, true)) {
            $module = null;
        }

        $count = $this->notifications->markAllRead($request->user(), $module);

        return redirect()
            ->route('core.notifications.index', array_filter(['module' => $module]))
            ->with('status', "已将 {$count} 条通知标记为已读");
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless($this->notifications->delete($request->user(), $id), 404, '通知不存在或不属于当前账号。');

        return back()->with('status', '通知已删除');
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'total' => $this->notifications->unreadCount($user),
            'by_module' => $this->notifications->unreadByModule($user),
        ]);
    }
}
