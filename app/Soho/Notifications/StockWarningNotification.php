<?php

namespace App\Soho\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 库存预警通知类
 * 用于商品库存低于阈值时发送通知
 */
class StockWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 创建通知实例
     *
     * @param array $goodsData 商品预警数据
     */
    public function __construct(
        protected array $goodsData
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
        // 支持邮件和数据库通知
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
        $goodsList = $this->goodsData;
        $count = count($goodsList);

        $mail = (new MailMessage)
            ->subject("【库存预警】{$count} 个商品库存不足")
            ->greeting('尊敬的管理员：')
            ->line('以下商品库存已低于设定的预警阈值，请及时补货：')
            ->line('');

        // 添加商品详情（最多显示10个）
        foreach (array_slice($goodsList, 0, 10) as $goods) {
            $mail->line("- **{$goods['goods_name']}** (SKU: {$goods['system_sku']})");
            $mail->line("  当前库存：{$goods['current_stock']}，预警阈值：{$goods['min_warn']}");
        }

        if ($count > 10) {
            $mail->line("... 还有 {$count} 个商品库存不足，请登录系统查看详情");
        }

        $mail->action('查看库存详情', url('/admin/system-goods?stock_warning=1'))
            ->line('请及时处理，避免影响正常销售。')
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
        // 修复字符串插值中count()函数调用问题：PHP双引号字符串中花括号内不支持函数调用
        $goodsCount = count($this->goodsData);
        
        return [
            'type' => 'stock_warning',
            'title' => '库存预警通知',
            'message' => "检测到 {$goodsCount} 个商品库存不足，请及时补货",
            'data' => [
                'goods_count' => $goodsCount,
                'goods_list' => array_slice($this->goodsData, 0, 5),
                'action_url' => '/admin/system-goods?stock_warning=1',
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
        return '库存预警通知';
    }
}