<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配送方式模型
 * 管理不同的配送方式，如快递、自提、同城配送等
 */
class ShippingMethod extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'status',
        'sort',
    ];

    /**
     * 属性类型转换
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * 获取计费类型的中文标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'fixed' => '固定运费',
            'weight' => '按重量计费',
            'quantity' => '按件数计费',
            'region' => '按地区计费',
            default => $this->type ?? '未知',
        };
    }

    /**
     * 获取状态的中文标签
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? '启用' : '禁用';
    }

    /**
     * 获取状态对应的颜色样式
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status ? 'success' : 'secondary';
    }

    /**
     * 判断是否启用中
     */
    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->status;
    }
}
