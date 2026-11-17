<?php

namespace App\Core\Services;

use App\Core\Models\Notification;
use App\Core\Models\User;

/**
 * 通知服务（TC-09）——三模块唯一发送/读取入口。
 *
 * 原系统各自的实现（erp NotificationService、pm NoticeService、soho NotificationService）
 * 在迁入时改为调用本服务，不再各自写表。
 */
class NotificationService
{
    /** 允许的来源模块（防止把错模块的值写进 module 列导致过滤失效） */
    public const MODULES = ['core', 'erp', 'pm', 'soho'];

    /**
     * 发一条通知。
     *
     * @param  array<string,mixed>  $data  原系统附加数据（erp data / soho data_json）
     */
    public function send(
        User|int $user,
        string $module,
        string $type,
        string $title,
        ?string $content = null,
        ?string $link = null,
        array $data = [],
        string $priority = 'normal',
        User|int|null $sender = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $tenantId = null,
    ): Notification {
        if (! in_array($module, self::MODULES, true)) {
            throw new \InvalidArgumentException('未知通知来源模块：'.$module);
        }

        return Notification::create([
            'user_id' => $user instanceof User ? $user->id : $user,
            'sender_id' => $sender instanceof User ? $sender->id : $sender,
            'module' => $module,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'data' => $data ?: null,
            'link' => $link,
            'priority' => $priority,
            'is_read' => false,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'tenant_id' => $tenantId,
        ]);
    }

    /** 未读数（可按模块过滤） */
    public function unreadCount(User $user, ?string $module = null): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->forModule($module)
            ->count();
    }

    /** 未读数按模块分组，供角标与门户展示 */
    public function unreadByModule(User $user): array
    {
        $rows = Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->selectRaw('module, count(*) as total')
            ->groupBy('module')
            ->pluck('total', 'module')
            ->all();

        $out = ['core' => 0, 'erp' => 0, 'pm' => 0, 'soho' => 0];
        foreach ($rows as $module => $total) {
            $out[$module ?: 'core'] = (int) $total;
        }
        $out['total'] = array_sum(array_intersect_key($out, array_flip(self::MODULES)));

        return $out;
    }

    /** 各模块总条数（含已读），供通知页 tab 上的计数，跟列表长度对齐 */
    public function totalByModule(User $user): array
    {
        $rows = Notification::query()
            ->where('user_id', $user->id)
            ->selectRaw('module, count(*) as total')
            ->groupBy('module')
            ->pluck('total', 'module')
            ->all();

        $out = ['core' => 0, 'erp' => 0, 'pm' => 0, 'soho' => 0];
        foreach ($rows as $module => $total) {
            $out[$module ?: 'core'] = (int) $total;
        }
        $out['total'] = array_sum(array_intersect_key($out, array_flip(self::MODULES)));

        return $out;
    }

    /**
     * 列表查询（分页由控制器做）。
     */
    public function queryFor(User $user, ?string $module = null, ?bool $unreadOnly = null)
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->forModule($module)
            ->when($unreadOnly === true, fn ($q) => $q->where('is_read', false))
            ->when($unreadOnly === false, fn ($q) => $q->where('is_read', true))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /** 标记单条已读（只允许操作自己的通知） */
    public function markRead(User $user, int $id): ?Notification
    {
        $notification = Notification::where('user_id', $user->id)->find($id);
        $notification?->markAsRead();

        return $notification;
    }

    /** 全部已读，返回影响条数 */
    public function markAllRead(User $user, ?string $module = null): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->forModule($module)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /** 删除单条（仅限本人） */
    public function delete(User $user, int $id): bool
    {
        return (bool) Notification::where('user_id', $user->id)->where('id', $id)->delete();
    }
}
