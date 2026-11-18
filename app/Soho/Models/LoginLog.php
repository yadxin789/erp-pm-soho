<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登录日志模型
 * 记录用户登录/登出事件
 */
class LoginLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    protected $fillable = [
        'user_id', 'email', 'status', 'ip_address', 'user_agent',
        'browser', 'os', 'device_type', 'login_at', 'logout_at',
        'session_id', 'login_type', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 登录状态标签
     */
    public static function getStatusLabels(): array
    {
        return [
            'success' => '登录成功',
            'failed' => '登录失败',
        ];
    }

    /**
     * 登录状态颜色
     */
    public static function getStatusColors(): array
    {
        return [
            'success' => 'success',
            'failed' => 'danger',
        ];
    }

    /**
     * 登录方式标签
     */
    public static function getLoginTypeLabels(): array
    {
        return [
            'password' => '密码登录',
            'otp' => '验证码登录',
            'oauth' => '第三方登录',
            'token' => '令牌登录',
        ];
    }

    /**
     * 设备类型标签
     */
    public static function getDeviceTypeLabels(): array
    {
        return [
            'desktop' => '桌面',
            'mobile' => '手机',
            'tablet' => '平板',
        ];
    }

    /**
     * 作用域：仅成功登录
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * 作用域：仅失败登录
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * 作用域：按用户筛选
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 作用域：按IP筛选
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * 获取登录方式标签
     */
    public function getLoginTypeLabel(): string
    {
        return self::getLoginTypeLabels()[$this->login_type] ?? $this->login_type;
    }

    /**
     * 计算在线时长（分钟）
     */
    public function getOnlineDuration(): ?int
    {
        if (!$this->login_at || !$this->logout_at) {
            return null;
        }
        return $this->login_at->diffInMinutes($this->logout_at);
    }

    /**
     * 是否当前在线（已登录未登出）
     */
    public function isOnline(): bool
    {
        return $this->status === 'success' && $this->login_at && !$this->logout_at;
    }
}