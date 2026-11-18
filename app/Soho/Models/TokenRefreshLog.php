<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Token刷新记录模型
 * 记录所有Token刷新历史，用于监控Token生命周期和排查刷新问题
 */
class TokenRefreshLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    /**
     * 允许批量赋值的字段
     */
    protected $fillable = [
        'tenant_id',
        'shop_id',
        'platform_account_id',
        'target_type',
        'target_id',
        'platform',
        'result',
        'refreshed_at',
        'refresh_method',
        'operator_id',
        'old_token_expires_at',
        'new_token_expires_at',
        'token_lifetime_seconds',
        'remaining_seconds',
        'error_message',
        'error_code',
        'ip_address',
        'extra_data',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'refreshed_at' => 'datetime',
            'old_token_expires_at' => 'datetime',
            'new_token_expires_at' => 'datetime',
            'extra_data' => 'array',
        ];
    }

    /**
     * 关联租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 关联店铺
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * 关联平台账号
     */
    public function platformAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    /**
     * 关联操作人
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * 刷新结果标签映射
     */
    public static function getResultLabels(): array
    {
        return [
            'success' => '成功',
            'failed' => '失败',
        ];
    }

    /**
     * 刷新结果颜色映射
     */
    public static function getResultColors(): array
    {
        return [
            'success' => 'success',
            'failed' => 'danger',
        ];
    }

    /**
     * 刷新方式标签映射
     */
    public static function getRefreshMethodLabels(): array
    {
        return [
            'auto' => '自动刷新',
            'manual' => '手动刷新',
            'scheduled' => '定时刷新',
        ];
    }

    /**
     * 刷新方式颜色映射
     */
    public static function getRefreshMethodColors(): array
    {
        return [
            'auto' => 'primary',
            'manual' => 'info',
            'scheduled' => 'success',
        ];
    }

    /**
     * 刷新方式图标映射
     */
    public static function getRefreshMethodIcons(): array
    {
        return [
            'auto' => 'fa-cog',
            'manual' => 'fa-hand-pointer',
            'scheduled' => 'fa-clock',
        ];
    }

    /**
     * 目标类型标签映射
     */
    public static function getTargetTypeLabels(): array
    {
        return [
            'shop' => '店铺',
            'platform_account' => '平台账号',
        ];
    }

    /**
     * 获取刷新结果标签
     */
    public function getResultLabel(): string
    {
        return self::getResultLabels()[$this->result] ?? $this->result;
    }

    /**
     * 获取刷新方式标签
     */
    public function getRefreshMethodLabel(): string
    {
        return self::getRefreshMethodLabels()[$this->refresh_method] ?? $this->refresh_method;
    }

    /**
     * 获取目标类型标签
     */
    public function getTargetTypeLabel(): string
    {
        return self::getTargetTypeLabels()[$this->target_type] ?? $this->target_type;
    }

    /**
     * 计算Token有效期（天）
     */
    public function getTokenLifetimeDays(): ?float
    {
        if (!$this->token_lifetime_seconds) {
            return null;
        }
        return round($this->token_lifetime_seconds / 86400, 1);
    }

    /**
     * 计算刷新前剩余有效时间（小时）
     */
    public function getRemainingHours(): ?float
    {
        if (!$this->remaining_seconds) {
            return null;
        }
        return round($this->remaining_seconds / 3600, 1);
    }

    /**
     * 是否接近过期刷新（剩余时间少于1天）
     */
    public function isUrgentRefresh(): bool
    {
        return $this->remaining_seconds && $this->remaining_seconds < 86400;
    }

    /**
     * 作用域：按租户筛选
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * 作用域：按店铺筛选
     */
    public function scopeByShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * 作用域：按平台账号筛选
     */
    public function scopeByPlatformAccount($query, int $platformAccountId)
    {
        return $query->where('platform_account_id', $platformAccountId);
    }

    /**
     * 作用域：按结果筛选
     */
    public function scopeByResult($query, string $result)
    {
        return $query->where('result', $result);
    }

    /**
     * 作用域：仅成功的刷新
     */
    public function scopeSuccess($query)
    {
        return $query->where('result', 'success');
    }

    /**
     * 作用域：仅失败的刷新
     */
    public function scopeFailed($query)
    {
        return $query->where('result', 'failed');
    }

    /**
     * 作用域：按刷新方式筛选
     */
    public function scopeByRefreshMethod($query, string $refreshMethod)
    {
        return $query->where('refresh_method', $refreshMethod);
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeInTimeRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->where('refreshed_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('refreshed_at', '<=', $dateTo . ' 23:59:59');
        }
        return $query;
    }
}