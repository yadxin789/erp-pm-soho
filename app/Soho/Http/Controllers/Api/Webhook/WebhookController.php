<?php

namespace App\Soho\Http\Controllers\Api\Webhook;

use App\Soho\Jobs\ProcessWebhookEventJob;
use App\Soho\Models\WebhookEventLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook控制器
 * 处理各平台事件推送，支持签名校验、幂等性和异步处理
 */
class WebhookController extends Controller
{
    /**
     * 处理平台事件
     */
    public function handle(Request $request, string $platform)
    {
        // 签名校验
        if (!$this->verifySignature($request, $platform)) {
            Log::warning('Webhook signature verification failed', ['platform' => $platform]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventId = $this->extractEventId($request, $platform);
        $eventType = $this->extractEventType($request, $platform);

        if (WebhookEventLog::isProcessed($platform, $eventId)) {
            Log::info('Webhook event already processed (idempotent check)', [
                'platform' => $platform,
                'event_id' => $eventId,
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        $log = WebhookEventLog::record($platform, $eventId, $eventType);
        ProcessWebhookEventJob::dispatch($platform, $eventType, $request->all(), $log);

        return response()->json(['status' => 'received']);
    }

    /**
     * 处理订单事件
     */
    public function order(Request $request, string $platform)
    {
        return $this->handleEvent($request, $platform, 'order');
    }

    /**
     * 处理库存事件
     */
    public function inventory(Request $request, string $platform)
    {
        return $this->handleEvent($request, $platform, 'inventory');
    }

    /**
     * 处理售后事件
     */
    public function afterSale(Request $request, string $platform)
    {
        return $this->handleEvent($request, $platform, 'after_sale');
    }

    /**
     * 通用事件处理方法
     */
    protected function handleEvent(Request $request, string $platform, string $eventType)
    {
        // 签名校验
        if (!$this->verifySignature($request, $platform)) {
            Log::warning('Webhook signature verification failed', ['platform' => $platform, 'event_type' => $eventType]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventId = $this->extractEventId($request, $platform);

        if (WebhookEventLog::isProcessed($platform, $eventId)) {
            Log::info('Webhook event already processed (idempotent check)', [
                'platform' => $platform,
                'event_type' => $eventType,
                'event_id' => $eventId,
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        $log = WebhookEventLog::record($platform, $eventId, $eventType);
        ProcessWebhookEventJob::dispatch($platform, $eventType, $request->all(), $log);

        Log::info('Webhook event received and queued', [
            'platform' => $platform,
            'event_type' => $eventType,
            'event_id' => $eventId,
        ]);

        return response()->json(['status' => 'received']);
    }

    /**
     * 校验平台Webhook签名
     *
     * @param Request $request 请求对象
     * @param string $platform 平台标识
     * @return bool 签名是否有效
     */
    protected function verifySignature(Request $request, string $platform): bool
    {
        $verifyToken = config("platforms.{$platform}.webhook.verify_token");

        // 未配置verify_token时跳过校验（开发环境）
        if (empty($verifyToken)) {
            Log::debug('Webhook signature skipped (no verify_token configured)', ['platform' => $platform]);
            return true;
        }

        $payload = $request->getContent();

        return match ($platform) {
            'taobao' => $this->verifyTaobaoSignature($request, $payload, $verifyToken),
            'jd' => $this->verifyJdSignature($request, $payload, $verifyToken),
            'pinduoduo' => $this->verifyPddSignature($request, $payload, $verifyToken),
            'amazon' => $this->verifyAmazonSignature($request, $payload, $verifyToken),
            default => true,
        };
    }

    /**
     * 淘宝签名校验：使用md5(secret + body + secret)
     */
    protected function verifyTaobaoSignature(Request $request, string $payload, string $verifyToken): bool
    {
        $sign = $request->header('X-Taobao-Sign') ?? $request->input('sign', '');
        if (empty($sign)) {
            return false;
        }
        $expected = strtoupper(md5($verifyToken . $payload . $verifyToken));
        return hash_equals($expected, strtoupper($sign));
    }

    /**
     * 京东签名校验：使用md5(secret + body + secret)
     */
    protected function verifyJdSignature(Request $request, string $payload, string $verifyToken): bool
    {
        $sign = $request->header('X-Jd-Sign') ?? $request->input('sign', '');
        if (empty($sign)) {
            return false;
        }
        $expected = strtoupper(md5($verifyToken . $payload . $verifyToken));
        return hash_equals($expected, strtoupper($sign));
    }

    /**
     * 拼多多签名校验：使用md5(client_secret + params + client_secret)
     */
    protected function verifyPddSignature(Request $request, string $payload, string $verifyToken): bool
    {
        $sign = $request->input('sign', '');
        if (empty($sign)) {
            return false;
        }
        // 拼多多签名基于参数排序拼接
        $params = $request->except('sign');
        ksort($params);
        $signStr = $verifyToken . http_build_query($params) . $verifyToken;
        $expected = strtoupper(md5($signStr));
        return hash_equals($expected, strtoupper($sign));
    }

    /**
     * Amazon签名校验：使用HMAC-SHA256
     */
    protected function verifyAmazonSignature(Request $request, string $payload, string $verifyToken): bool
    {
        $signature = $request->header('X-Amz-Signature') ?? $request->input('signature', '');
        if (empty($signature)) {
            return false;
        }
        $expected = hash_hmac('sha256', $payload, $verifyToken);
        return hash_equals($expected, $signature);
    }

    /**
     * 从请求中提取事件ID（不同平台格式不同）
     */
    protected function extractEventId(Request $request, string $platform): string
    {
        $data = $request->all();

        return match ($platform) {
            'taobao' => $data['event_id'] ?? $data['id'] ?? $this->generateFallbackId($data),
            'jd' => $data['eventId'] ?? $data['id'] ?? $this->generateFallbackId($data),
            'pinduoduo' => $data['event_id'] ?? $data['order_sn'] ?? $this->generateFallbackId($data),
            'amazon' => $data['NotificationId'] ?? $data['orderId'] ?? $this->generateFallbackId($data),
            default => $data['event_id'] ?? $data['id'] ?? $this->generateFallbackId($data),
        };
    }

    /**
     * 从请求中提取事件类型
     */
    protected function extractEventType(Request $request, string $platform): string
    {
        $data = $request->all();

        return match ($platform) {
            'taobao' => $data['event_type'] ?? 'unknown',
            'jd' => $data['eventType'] ?? 'unknown',
            'pinduoduo' => $data['type'] ?? 'unknown',
            'amazon' => $data['NotificationType'] ?? 'unknown',
            default => 'unknown',
        };
    }

    /**
     * 生成备选事件ID
     */
    protected function generateFallbackId(array $data): string
    {
        return md5(json_encode($data) . microtime());
    }
}