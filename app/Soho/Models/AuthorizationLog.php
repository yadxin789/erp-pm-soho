<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 授权操作日志模型
 * 记录所有授权操作历史，包括授权、重新授权、撤销授权等操作
 */
class AuthorizationLog extends Model
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
        'operation_type',
        'target_type',
        'target_id',
        'operator_id',
        'operator_name',
        'result',
        'operated_at',
        'ip_address',
        'user_agent',
        'detail',
        'error_message',
        'extra_data',
    ];

    /**
     * 类型转换
     */
    protected function casts(): array
    {
        return [
            'operated_at' => 'datetime',
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
     * 操作类型标签映射
     */
    public static function getOperationTypeLabels(): array
    {
        return [
            'authorize' => '授权',
            'reauthorize' => '重新授权',
            'revoke' => '撤销授权',
            'refresh' => '刷新授权',
            'token_refresh' => '令牌刷新',
        ];
    }

    /**
     * 操作类型颜色映射
     */
    public static function getOperationTypeColors(): array
    {
        return [
            'authorize' => 'success',
            'reauthorize' => 'info',
            'revoke' => 'danger',
            'refresh' => 'warning',
            'token_refresh' => 'primary',
        ];
    }

    /**
     * 操作类型图标映射
     */
    public static function getOperationTypeIcons(): array
    {
        return [
            'authorize' => 'fa-link',
            'reauthorize' => 'fa-sync',
            'revoke' => 'fa-unlink',
            'refresh' => 'fa-redo',
            'token_refresh' => 'fa-key',
        ];
    }

    /**
     * 操作结果标签映射
     */
    public static function getResultLabels(): array
    {
        return [
            'success' => '成功',
            'failed' => '失败',
        ];
    }

    /**
     * 操作结果颜色映射
     */
    public static function getResultColors(): array
    {
        return [
            'success' => 'success',
            'failed' => 'danger',
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
     * 获取操作类型标签
     */
    public function getOperationTypeLabel(): string
    {
        return self::getOperationTypeLabels()[$this->operation_type] ?? $this->operation_type;
    }

    /**
     * 获取操作结果标签
     */
    public function getResultLabel(): string
    {
        return self::getResultLabels()[$this->result] ?? $this->result;
    }

    /**
     * 获取目标类型标签
     */
    public function getTargetTypeLabel(): string
    {
        return self::getTargetTypeLabels()[$this->target_type] ?? $this->target_type;
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
     * 作用域：按操作类型筛选
     */
    public function scopeByOperationType($query, string $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    /**
     * 作用域：按结果筛选
     */
    public function scopeByResult($query, string $result)
    {
        return $query->where('result', $result);
    }

    /**
     * 作用域：仅成功的操作
     */
    public function scopeSuccess($query)
    {
        return $query->where('result', 'success');
    }

    /**
     * 作用域：仅失败的操作
     */
    public function scopeFailed($query)
    {
        return $query->where('result', 'failed');
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeInTimeRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->where('operated_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('operated_at', '<=', $dateTo . ' 23:59:59');
        }
        return $query;
    }
}