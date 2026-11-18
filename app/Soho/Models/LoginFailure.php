<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登录失败记录模型
 * 记录所有登录失败尝试，用于安全审计和暴力破解检测
 */
class LoginFailure extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    /**
     * 表名
     */
    protected $table = 'soho_login_failures';

    /**
     * 只包含 created_at，不自动维护 updated_at
     */
    public $timestamps = false;

    /**
     * 允许批量赋值的字段
     */
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'failure_reason',
        'detail',
        'triggered_lockout',
        'created_at',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'triggered_lockout' => 'boolean',
            'created_at' => 'datetime',
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
     * 失败原因标签映射
     */
    public static function getReasonLabels(): array
    {
        return [
            'invalid_password' => '密码错误',
            'password_expired' => '密码已过期',
            'account_locked' => '账号已锁定',
            'account_not_found' => '账号不存在',
            'throttle' => '请求过于频繁',
            'invalid_otp' => '验证码错误',
            'mfa_failed' => '二次验证失败',
            'ip_blocked' => 'IP已被封禁',
        ];
    }

    /**
     * 失败原因颜色映射
     */
    public static function getReasonColors(): array
    {
        return [
            'invalid_password' => 'warning',
            'password_expired' => 'info',
            'account_locked' => 'danger',
            'account_not_found' => 'secondary',
            'throttle' => 'warning',
            'invalid_otp' => 'warning',
            'mfa_failed' => 'danger',
            'ip_blocked' => 'danger',
        ];
    }

    /**
     * 失败原因图标映射
     */
    public static function getReasonIcons(): array
    {
        return [
            'invalid_password' => 'fa-key',
            'password_expired' => 'fa-clock',
            'account_locked' => 'fa-lock',
            'account_not_found' => 'fa-user-times',
            'throttle' => 'fa-tachometer-alt',
            'invalid_otp' => 'fa-shield',
            'mfa_failed' => 'fa-mobile',
            'ip_blocked' => 'fa-ban',
        ];
    }

    /**
     * 获取失败原因标签
     */
    public function getReasonLabel(): string
    {
        $labels = self::getReasonLabels();
        return $labels[$this->failure_reason] ?? $this->failure_reason;
    }

    /**
     * 作用域：按IP筛选
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 作用域：按邮箱筛选
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeInTimeRange($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * 作用域：触发锁定的记录
     */
    public function scopeTriggeredLockout($query)
    {
        return $query->where('triggered_lockout', true);
    }
}