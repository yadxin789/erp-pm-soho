<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 授权失败记录模型
 * 记录所有授权失败事件，用于问题排查和告警
 */
class AuthorizationFailure extends Model
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
        'failure_type',
        'target_type',
        'target_id',
        'error_code',
        'error_message',
        'platform',
        'failed_at',
        'retry_count',
        'max_retries',
        'status',
        'resolved_at',
        'resolved_by',
        'resolution_note',
        'request_params',
        'response_data',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'request_params' => 'array',
            'response_data' => 'array',
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
     * 关联解决人
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * 失败类型标签映射
     */
    public static function getFailureTypeLabels(): array
    {
        return [
            'authorize' => '授权失败',
            'refresh' => '刷新失败',
            'revoke' => '撤销失败',
            'token_expired' => '令牌过期',
            'network' => '网络错误',
            'permission' => '权限不足',
            'rate_limit' => '限流',
            'invalid_code' => '无效授权码',
        ];
    }

    /**
     * 失败类型颜色映射
     */
    public static function getFailureTypeColors(): array
    {
        return [
            'authorize' => 'danger',
            'refresh' => 'warning',
            'revoke' => 'warning',
            'token_expired' => 'info',
            'network' => 'secondary',
            'permission' => 'danger',
            'rate_limit' => 'warning',
            'invalid_code' => 'danger',
        ];
    }

    /**
     * 失败类型图标映射
     */
    public static function getFailureTypeIcons(): array
    {
        return [
            'authorize' => 'fa-link',
            'refresh' => 'fa-redo',
            'revoke' => 'fa-unlink',
            'token_expired' => 'fa-clock',
            'network' => 'fa-wifi',
            'permission' => 'fa-lock',
            'rate_limit' => 'fa-tachometer-alt',
            'invalid_code' => 'fa-code',
        ];
    }

    /**
     * 解决状态标签映射
     */
    public static function getStatusLabels(): array
    {
        return [
            'pending' => '待处理',
            'resolving' => '处理中',
            'resolved' => '已解决',
            'ignored' => '已忽略',
        ];
    }

    /**
     * 解决状态颜色映射
     */
    public static function getStatusColors(): array
    {
        return [
            'pending' => 'danger',
            'resolving' => 'warning',
            'resolved' => 'success',
            'ignored' => 'secondary',
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
     * 获取失败类型标签
     */
    public function getFailureTypeLabel(): string
    {
        return self::getFailureTypeLabels()[$this->failure_type] ?? $this->failure_type;
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * 获取目标类型标签
     */
    public function getTargetTypeLabel(): string
    {
        return self::getTargetTypeLabels()[$this->target_type] ?? $this->target_type;
    }

    /**
     * 是否可以重试
     */
    public function canRetry(): bool
    {
        return $this->retry_count < $this->max_retries && $this->status === 'pending';
    }

    /**
     * 是否已解决
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * 是否待处理
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
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
     * 作用域：按失败类型筛选
     */
    public function scopeByFailureType($query, string $failureType)
    {
        return $query->where('failure_type', $failureType);
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：仅待处理
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 作用域：仅已解决
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeInTimeRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->where('failed_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('failed_at', '<=', $dateTo . ' 23:59:59');
        }
        return $query;
    }
}