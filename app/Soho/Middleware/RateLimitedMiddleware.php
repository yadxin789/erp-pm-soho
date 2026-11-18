<?php

namespace App\Soho\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitedMiddleware
{
    protected string $key;
    protected int $maxAttempts;
    protected int $decaySeconds;

    /**
     * @param string $key      缓存键名
     * @param int $maxAttempts 最大尝试次数
     * @param int $decaySeconds 时间窗口（秒）
     */
    public function __construct(string $key = 'api-rate-limit', int $maxAttempts = 100, int $decaySeconds = 60)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    /**
     * 处理队列任务执行前的速率限制检查
     */
    public function handle(object $job, \Closure $next): mixed
    {
        $lockKey = "rate_limit:{$this->key}:" . date('YmdHi', (int)(time() / $this->decaySeconds) * $this->decaySeconds);

        $currentCount = Cache::get($lockKey, 0);

        if ($currentCount >= $this->maxAttempts) {
            // 超过速率限制，将任务延迟释放回队列
            Log::warning("API 速率限制触发", [
                'key'          => $this->key,
                'current_count'=> $currentCount,
                'max_attempts' => $this->maxAttempts,
                'job'          => get_class($job),
            ]);

            // 延迟到下一个时间窗口
            $delay = $this->decaySeconds;
            $job->release($delay);
            return $next($job);
        }

        // 增加计数器
        Cache::put($lockKey, $currentCount + 1, $this->decaySeconds);

        try {
            return $next($job);
        } finally {
            // 请求完成后不减少计数，依靠时间窗口自然过期
        }
    }

    /**
     * 创建淘宝 API 限流中间件（每分钟 5000 次，安全上限 1000 次/秒为 60次/分钟窗口）
     */
    public static function forTaobao(int $windowSeconds = 60, int $maxRequests = 300): self
    {
        return new self('taobao-api', $maxRequests, $windowSeconds);
    }

    /**
     * 创建京东 API 限流中间件
     */
    public static function forJd(int $windowSeconds = 60, int $maxRequests = 200): self
    {
        return new self('jd-api', $maxRequests, $windowSeconds);
    }

    /**
     * 创建拼多多 API 限流中间件
     */
    public static function forPinduoduo(int $windowSeconds = 60, int $maxRequests = 100): self
    {
        return new self('pinduoduo-api', $maxRequests, $windowSeconds);
    }

    /**
     * 创建亚马逊 API 限流中间件（SP-API 限制更严）
     */
    public static function forAmazon(int $windowSeconds = 60, int $maxRequests = 50): self
    {
        return new self('amazon-api', $maxRequests, $windowSeconds);
    }

    public static function forPlatform(string $platform, int $windowSeconds = 60): self
    {
        return match ($platform) {
            'taobao'    => self::forTaobao($windowSeconds),
            'jd'        => self::forJd($windowSeconds),
            'pinduoduo' => self::forPinduoduo($windowSeconds),
            'amazon'    => self::forAmazon($windowSeconds),
            default     => self::forTaobao($windowSeconds),
        };
    }
}
