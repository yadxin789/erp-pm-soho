<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 优惠券模型
 * 管理各类优惠券，支持满减、折扣等类型
 */
class Coupon extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'type',
        'value',
        'min_amount',
        'total_quantity',
        'used_quantity',
        'start_at',
        'end_at',
        'status',
        'applicable_products',
        'applicable_categories',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'total_quantity' => 'integer',
            'used_quantity' => 'integer',
            'applicable_products' => 'array',
            'applicable_categories' => 'array',
        ];
    }

    /**
     * 获取该优惠券的使用记录
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * 访问器：类型中文标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'fixed_discount' => '固定金额',
            'percentage' => '百分比折扣',
            'shipping' => '包邮',
            default => $this->type,
        };
    }

    /**
     * 访问器：状态中文标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => '启用',
            'inactive' => '停用',
            'expired' => '已过期',
            default => $this->status,
        };
    }

    /**
     * 访问器：状态徽章颜色
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * 访问器：剩余数量
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->used_quantity);
    }

    /**
     * 访问器：是否有效
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $now = now();
        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }
        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }
        return $this->remaining_quantity > 0;
    }

    /**
     * 访问器：优惠描述
     */
    public function getDescriptionAttribute(): string
    {
        return match($this->type) {
            'fixed_discount' => '满 ¥' . ($this->min_amount ?? 0) . ' 减 ¥' . $this->value,
            'percentage' => '满 ¥' . ($this->min_amount ?? 0) . ' 享 ' . $this->value . '% 折扣',
            'shipping' => '包邮（限 ¥' . ($this->min_amount ?? 0) . ' 以上订单）',
            default => $this->type,
        };
    }

    /**
     * 检查优惠券当前是否有效
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $now = now();
        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }
        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }
        if ($this->used_quantity >= $this->total_quantity) {
            return false;
        }
        return true;
    }

    /**
     * 使用优惠券
     */
    public function use(): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        $this->used_quantity++;
        if ($this->used_quantity >= $this->total_quantity) {
            $this->status = 'inactive';
        }
        return $this->save();
    }
}
