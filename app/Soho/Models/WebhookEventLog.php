<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Webhook事件日志模型
 * 用于记录已处理的Webhook事件，实现幂等性
 */
class WebhookEventLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'platform', 'event_id', 'event_type', 'status', 'processed_at', 'error_message',
    ];

    /**
     * 检查事件是否已处理（幂等性校验）
     *
     * @param string $platform 平台标识
     * @param string $eventId 事件ID
     * @return bool 是否已处理
     */
    public static function isProcessed(string $platform, string $eventId): bool
    {
        return self::where('platform', $platform)
            ->where('event_id', $eventId)
            ->exists();
    }

    /**
     * 记录事件处理开始
     *
     * @param string $platform 平台标识
     * @param string $eventId 事件ID
     * @param string $eventType 事件类型
     * @return self
     */
    public static function record(string $platform, string $eventId, string $eventType): self
    {
        return self::create([
            'platform' => $platform,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'status' => 'processing',
        ]);
    }

    /**
     * 标记事件处理成功
     */
    public function markSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'processed_at' => now(),
        ]);
    }

    /**
     * 标记事件处理失败
     *
     * @param string $errorMessage 错误信息
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'processed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }
}
