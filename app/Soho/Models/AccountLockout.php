<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 账号锁定记录模型
 * 记录账号锁定事件，用于安全审计和账号管理
 */
class AccountLockout extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    /**
     * 允许批量赋值的字段
     */
    protected $fillable = [
        'user_id',
        'lock_type',
        'reason',
        'ip_address',
        'locked_at',
        'lock_duration',
        'lock_expires_at',
        'unlocked_by',
        'unlocked_at',
        'unlock_type',
        'unlock_note',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'lock_expires_at' => 'datetime',
            'unlocked_at' => 'datetime',
            'lock_duration' => 'integer',
        ];
    }

    /**
     * 关联被锁定的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联解锁人
     */
    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * 锁定类型标签
     */
    public static function getLockTypeLabels(): array
    {
        return [
            'manual' => '管理员锁定',
            'throttle' => '频繁失败自动锁定',
            'password_wrong_too_many' => '密码错误次数过多',
            'password_expired' => '密码过期',
            'suspicious_activity' => '可疑活动',
        ];
    }

    /**
     * 锁定类型颜色
     */
    public static function getLockTypeColors(): array
    {
        return [
            'manual' => 'danger',
            'throttle' => 'warning',
            'password_wrong_too_many' => 'warning',
            'password_expired' => 'info',
            'suspicious_activity' => 'danger',
        ];
    }

    /**
     * 解锁方式标签
     */
    public static function getUnlockTypeLabels(): array
    {
        return [
            'manual' => '管理员解锁',
            'time_expired' => '自动过期',
            'password_reset' => '密码重置',
            'mfa_verified' => '二次验证通过',
        ];
    }

    /**
     * 是否已解锁
     */
    public function isUnlocked(): bool
    {
        return !is_null($this->unlocked_at);
    }

    /**
     * 是否为永久锁定
     */
    public function isPermanent(): bool
    {
        return $this->lock_duration === 0;
    }

    /**
     * 是否仍在锁定中
     */
    public function isActive(): bool
    {
        if ($this->isUnlocked()) {
            return false;
        }
        if ($this->isPermanent()) {
            return true;
        }
        return $this->lock_expires_at && $this->lock_expires_at->isFuture();
    }

    /**
     * 获取锁定类型标签
     */
    public function getLockTypeLabel(): string
    {
        return self::getLockTypeLabels()[$this->lock_type] ?? $this->lock_type;
    }

    /**
     * 作用域：当前有效的锁定
     */
    public function scopeActive($query)
    {
        return $query->whereNull('unlocked_at')
            ->where(function ($q) {
                $q->where('lock_duration', 0) // 永久锁定
                  ->orWhere('lock_expires_at', '>', now());
            });
    }

    /**
     * 作用域：已解锁的记录
     */
    public function scopeUnlocked($query)
    {
        return $query->whereNotNull('unlocked_at');
    }
}