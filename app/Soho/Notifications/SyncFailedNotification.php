<?php

namespace App\Soho\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 同步失败告警通知类
 * 用于订单同步、库存同步等任务失败时发送通知
 */
class SyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 创建通知实例
     *
     * @param array $syncData 同步失败数据
     */
    public function __construct(
        protected array $syncData
    ) {
        $this->onQueue('default');
    }

    /**
     * 获取通知投递渠道
     *
     * @param mixed $notifiable 通知接收者
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件通知内容
     *
     * @param mixed $notifiable 通知接收者
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $sync = $this->syncData;
        $typeLabel = $this->getTypeLabel($sync['task_type']);

        $shopName = $sync['shop_name'] ?? '全部店铺';

        $mail = (new MailMessage)
            ->subject("【同步失败】{$typeLabel}任务执行失败")
            ->greeting('尊敬的管理员：')
            ->line("系统检测到同步任务执行失败，请及时处理：")
            ->line('')
            ->line("**任务信息：**")
            ->line("- 任务类型：{$typeLabel}")
            ->line("- 店铺名称：{$shopName}")
            ->line("- 失败时间：{$sync['failed_at']}")
            ->line("- 重试次数：{$sync['attempts']}次");

        if (!empty($sync['error_message'])) {
            $mail->line('')
                ->line("**错误信息：**")
                ->line($sync['error_message']);
        }

        $mail->line('')
            ->error('该任务多次重试后仍然失败，请检查API配置或网络连接。')
            ->action('查看同步日志', url('/admin/sync-task-logs'))
            ->line('请及时处理，避免影响数据同步准确性。')
            ->line('此邮件由系统自动发送，请勿回复。');

        return $mail;
    }

    /**
     * 获取数据库通知内容
     *
     * @param mixed $notifiable 通知接收者
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        $sync = $this->syncData;
        $typeLabel = $this->getTypeLabel($sync['task_type']);
        $shopName = $sync['shop_name'] ?? '全部';

        return [
            'type' => 'sync_failed',
            'title' => "{$typeLabel}任务执行失败",
            'message' => "店铺【{$shopName}】{$typeLabel}任务失败，请及时处理",
            'data' => [
                'task_type' => $sync['task_type'],
                'task_name' => $sync['task_name'] ?? $typeLabel,
                'shop_id' => $sync['shop_id'] ?? null,
                'shop_name' => $sync['shop_name'] ?? null,
                'error_message' => $sync['error_message'],
                'attempts' => $sync['attempts'],
                'failed_at' => $sync['failed_at'],
                'action_url' => '/admin/sync-task-logs',
            ],
        ];
    }

    /**
     * 获取任务类型标签
     *
     * @param string $taskType 任务类型
     * @return string
     */
    protected function getTypeLabel(string $taskType): string
    {
        return match ($taskType) {
            'order_sync' => '订单同步',
            'stock_sync' => '库存同步',
            'goods_push' => '商品推送',
            'shipment_push' => '发货推送',
            'token_refresh' => 'Token刷新',
            default => '同步任务',
        };
    }

    /**
     * 获取队列任务显示名称
     *
     * @return string
     */
    public function displayName(): string
    {
        return '同步失败告警通知';
    }
}