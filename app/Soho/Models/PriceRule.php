<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 价格规则模型
 * 管理商品的定价规则，支持多种规则类型（如加价、折扣、固定价格等）
 */
class PriceRule extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'description',
        'parameters',
        'min_price',
        'max_price',
        'is_active',
        'priority',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'is_active' => 'boolean',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取规则类型标签映射
     */
    public static function getTypeLabels(): array
    {
        return [
            'markup' => '加价规则',
            'discount' => '折扣规则',
            'fixed' => '固定价格',
            'range' => '区间定价',
            'tiered' => '阶梯定价',
            'competitor' => '竞品跟随',
            'cost_plus' => '成本加成',
        ];
    }

    /**
     * 获取规则类型对应的颜色标识
     */
    public static function getTypeColors(): array
    {
        return [
            'markup' => 'primary',
            'discount' => 'success',
            'fixed' => 'info',
            'range' => 'warning',
            'tiered' => 'purple',
            'competitor' => 'orange',
            'cost_plus' => 'secondary',
        ];
    }

    /**
     * 仅查询启用中的规则
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * 按规则类型过滤
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}