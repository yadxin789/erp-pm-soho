<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 促销活动模型
 * 管理店铺的促销活动信息，如满减、折扣等
 */
class Promotion extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'value',
        'min_amount',
        'start_at',
        'end_at',
        'status',
        'stackable',
        'conditions_json',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'conditions_json' => 'array',
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
     * 访问器：类型中文标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'fixed_discount' => '固定金额',
            'percentage' => '百分比折扣',
            'buy_get' => '买赠',
            'flash_sale' => '限时特惠',
            'bundle' => '组合优惠',
            default => $this->type,
        };
    }

    /**
     * 访问器：状态中文标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => '草稿',
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
            'draft' => 'warning',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * 访问器：是否正在进行中
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }
        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * 访问器：优惠描述
     */
    public function getDescriptionAttribute(): string
    {
        return match($this->type) {
            'fixed_discount' => '满 ¥' . ($this->min_amount ?? 0) . ' 减 ¥' . $this->value,
            'percentage' => '满 ¥' . ($this->min_amount ?? 0) . ' 享 ' . $this->value . '% 折扣',
            'buy_get' => '买赠活动',
            'flash_sale' => '限时特惠 ¥' . $this->value,
            'bundle' => '组合优惠 ¥' . $this->value,
            default => $this->type,
        };
    }
}
