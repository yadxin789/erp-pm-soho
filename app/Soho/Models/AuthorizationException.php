<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 授权异常模型
 * 记录所有授权相关异常，包括异常类型、详情、处理状态等
 */
class AuthorizationException extends Model
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
        'exception_type',
        'target_type',
        'target_id',
        'platform',
        'severity',
        'exception_at',
        'title',
        'detail',
        'error_code',
        'error_message',
        'status',
        'handled_at',
        'handled_by',
        'handler_name',
        'handle_method',
        'handle_note',
        'occurrence_count',
        'last_occurred_at',
        'extra_data',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'exception_at' => 'datetime',
            'handled_at' => 'datetime',
            'last_occurred_at' => 'datetime',
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
     * 关联处理人
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * 异常类型标签映射
     */
    public static function getExceptionTypeLabels(): array
    {
        return [
            'token_expired' => '令牌过期',
            'token_invalid' => '令牌无效',
            'permission_denied' => '权限被拒',
            'auth_failed' => '授权失败',
            'rate_limit_exceeded' => '限流超限',
            'api_error' => '接口错误',
            'callback_failed' => '回调失败',
            'network_error' => '网络错误',
            'other' => '其他',
        ];
    }

    /**
     * 异常类型颜色映射
     */
    public static function getExceptionTypeColors(): array
    {
        return [
            'token_expired' => 'warning',
            'token_invalid' => 'danger',
            'permission_denied' => 'danger',
            'auth_failed' => 'danger',
            'rate_limit_exceeded' => 'warning',
            'api_error' => 'warning',
            'callback_failed' => 'warning',
            'network_error' => 'secondary',
            'other' => 'info',
        ];
    }

    /**
     * 异常类型图标映射
     */
    public static function getExceptionTypeIcons(): array
    {
        return [
            'token_expired' => 'fa-clock',
            'token_invalid' => 'fa-key',
            'permission_denied' => 'fa-lock',
            'auth_failed' => 'fa-link',
            'rate_limit_exceeded' => 'fa-tachometer-alt',
            'api_error' => 'fa-code',
            'callback_failed' => 'fa-reply',
            'network_error' => 'fa-wifi',
            'other' => 'fa-exclamation-triangle',
        ];
    }

    /**
     * 异常级别标签映射
     */
    public static function getSeverityLabels(): array
    {
        return [
            'critical' => '严重',
            'warning' => '警告',
            'info' => '提示',
        ];
    }

    /**
     * 异常级别颜色映射
     */
    public static function getSeverityColors(): array
    {
        return [
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
        ];
    }

    /**
     * 处理状态标签映射
     */
    public static function getStatusLabels(): array
    {
        return [
            'pending' => '待处理',
            'handling' => '处理中',
            'handled' => '已处理',
            'ignored' => '已忽略',
        ];
    }

    /**
     * 处理状态颜色映射
     */
    public static function getStatusColors(): array
    {
        return [
            'pending' => 'danger',
            'handling' => 'warning',
            'handled' => 'success',
            'ignored' => 'secondary',
        ];
    }

    /**
     * 处理方式标签映射
     */
    public static function getHandleMethodLabels(): array
    {
        return [
            'manual' => '手动处理',
            'auto' => '自动处理',
            'system' => '系统处理',
            'ignored' => '忽略',
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
     * 获取异常类型标签
     */
    public function getExceptionTypeLabel(): string
    {
        return self::getExceptionTypeLabels()[$this->exception_type] ?? $this->exception_type;
    }

    /**
     * 获取异常级别标签
     */
    public function getSeverityLabel(): string
    {
        return self::getSeverityLabels()[$this->severity] ?? $this->severity;
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * 获取处理方式标签
     */
    public function getHandleMethodLabel(): string
    {
        return self::getHandleMethodLabels()[$this->handle_method] ?? $this->handle_method;
    }

    /**
     * 获取目标类型标签
     */
    public function getTargetTypeLabel(): string
    {
        return self::getTargetTypeLabels()[$this->target_type] ?? $this->target_type;
    }

    /**
     * 是否需要处理
     */
    public function needsHandling(): bool
    {
        return in_array($this->status, ['pending', 'handling']);
    }

    /**
     * 是否已处理
     */
    public function isHandled(): bool
    {
        return $this->status === 'handled';
    }

    /**
     * 是否是严重异常
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
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
     * 作用域：按异常类型筛选
     */
    public function scopeByExceptionType($query, string $exceptionType)
    {
        return $query->where('exception_type', $exceptionType);
    }

    /**
     * 作用域：按异常级别筛选
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
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
     * 作用域：仅已处理
     */
    public function scopeHandled($query)
    {
        return $query->where('status', 'handled');
    }

    /**
     * 作用域：仅严重异常
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeInTimeRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->where('exception_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('exception_at', '<=', $dateTo . ' 23:59:59');
        }
        return $query;
    }
}