<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 商品属性模型
 * 定义商品的属性模板，如颜色、尺寸等
 */
class ProductAttribute extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'type', 'options_json', 'sort', 'status',
    ];

    protected function casts(): array
    {
        return [
            'options_json' => 'array',
            'status' => 'integer',
            'sort' => 'integer',
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
     * 获取属性值列表
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class)->orderBy('sort_order');
    }

    /**
     * 访问器：类型中文标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'text' => '文本输入',
            'number' => '数字输入',
            'select' => '下拉选择',
            'multi_select' => '多选下拉',
            'date' => '日期选择',
            default => $this->type,
        };
    }

    /**
     * 访问器：状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? '启用' : '禁用';
    }

    /**
     * 访问器：选项数量
     */
    public function getOptionCountAttribute(): int
    {
        return $this->options_json ? count($this->options_json) : 0;
    }

    /**
     * 访问器：属性值数量
     */
    public function getValueCountAttribute(): int
    {
        return $this->values->count();
    }
}
