<?php

namespace App\Soho\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Token过期通知类
 * 用于电商平台授权令牌即将过期或已过期时发送通知
 */
class TokenExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 创建通知实例
     *
     * @param array $shopData 店铺Token数据
     * @param bool $isExpired 是否已过期（true:已过期 false:即将过期）
     */
    public function __construct(
        protected array $shopData,
        protected bool $isExpired = false
    ) {
        // 设置队列名称
        $this->onQueue('soho_notifications');
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
        $shop = $this->shopData;
        $statusText = $this->isExpired ? '已过期' : '即将过期';
        $urgency = $this->isExpired ? '紧急' : '重要';

        $mail = (new MailMessage)
            ->subject("【{$urgency}】店铺授权Token{$statusText} - {$shop['shop_name']}")
            ->greeting('尊敬的管理员：')
            ->line("您的店铺授权令牌{$statusText}，请及时处理：")
            ->line('')
            ->line("**店铺信息：**")
            ->line("- 店铺名称：{$shop['shop_name']}")
            ->line("- 所属平台：{$shop['platform_label']}")
            ->line("- Token过期时间：{$shop['token_expires_at']}");

        if ($this->isExpired) {
            $mail->line('')
                ->error('该店铺的授权令牌已过期，订单同步、库存同步等功能将无法正常使用！');
        } else {
            $mail->line('')
                ->warning('该店铺的授权令牌即将过期，请尽快刷新授权以保证业务正常运行。');
        }

        $mail->action('前往店铺管理', url('/admin/shops/' . $shop['id'] . '/edit'))
            ->line('请及时处理，避免影响店铺正常运营。')
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
        $shop = $this->shopData;
        $statusText = $this->isExpired ? '已过期' : '即将过期';

        return [
            'type' => 'token_expired',
            'title' => "店铺授权Token{$statusText}",
            'message' => "店铺【{$shop['shop_name']}】授权令牌{$statusText}，请及时处理",
            'data' => [
                'shop_id' => $shop['id'],
                'shop_name' => $shop['shop_name'],
                'platform' => $shop['platform'],
                'is_expired' => $this->isExpired,
                'expires_at' => $shop['token_expires_at'],
                'action_url' => '/admin/shops/' . $shop['id'] . '/edit',
            ],
        ];
    }

    /**
     * 获取队列任务显示名称
     *
     * @return string
     */
    public function displayName(): string
    {
        return 'Token过期通知';
    }
}