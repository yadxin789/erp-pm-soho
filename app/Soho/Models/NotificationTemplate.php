<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 通知模板模型
 * 管理系统各业务场景的通知消息模板
 * 支持邮件、短信、微信、系统消息等渠道
 * 模板内容支持变量占位符（如 {{order_no}}）
 */
class NotificationTemplate extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'template_code', 'template_name', 'channel',
        'business_type', 'subject', 'content', 'available_variables',
        'is_active', 'last_modified_by',
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function lastModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * 渠道标签
     */
    public static function getChannelLabels(): array
    {
        return [
            'email' => '邮件',
            'sms' => '短信',
            'wechat' => '微信',
            'system' => '系统消息',
        ];
    }

    public static function getChannelColors(): array
    {
        return [
            'email' => 'primary',
            'sms' => 'success',
            'wechat' => 'success',
            'system' => 'info',
        ];
    }

    public static function getChannelIcons(): array
    {
        return [
            'email' => 'fa-envelope',
            'sms' => 'fa-mobile',
            'wechat' => 'fa-weixin',
            'system' => 'fa-desktop',
        ];
    }

    /**
     * 业务类型标签
     */
    public static function getBusinessTypeLabels(): array
    {
        return [
            'order' => '订单',
            'after_sale' => '售后',
            'inventory' => '库存',
            'finance' => '财务',
            'system' => '系统',
            'other' => '其他',
        ];
    }

    /**
     * 渲染模板内容（替换变量占位符）
     * @param array $variables 变量键值对
     * @return string 渲染后的内容
     */
    public function renderContent(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * 渲染模板标题
     */
    public function renderSubject(array $variables = []): ?string
    {
        if (!$this->subject) {
            return null;
        }

        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', (string) $value, $subject);
        }

        return $subject;
    }
}
