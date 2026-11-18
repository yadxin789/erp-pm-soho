<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 功能开关模型
 * 控制系统各功能模块的启用/禁用状态
 * 可用于灰度发布、功能控制、紧急关闭等场景
 */
class FeatureSwitch extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'feature_key', 'feature_name', 'description',
        'module', 'is_enabled', 'config',
        'enabled_by', 'enabled_at', 'last_modified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
            'enabled_at' => 'datetime',
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
     * 关联启用人
     */
    public function enabler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enabled_by');
    }

    /**
     * 关联最后修改人
     */
    public function lastModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * 作用域：仅查询启用的功能
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * 作用域：按模块筛选
     */
    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * 模块标签映射
     */
    public static function getModuleLabels(): array
    {
        return [
            'order' => '订单',
            'product' => '商品',
            'inventory' => '库存',
            'logistics' => '物流',
            'finance' => '财务',
            'system' => '系统',
            'other' => '其他',
        ];
    }

    /**
     * 模块颜色映射（用于徽章显示）
     */
    public static function getModuleColors(): array
    {
        return [
            'order' => 'primary',
            'product' => 'success',
            'inventory' => 'info',
            'logistics' => 'warning',
            'finance' => 'danger',
            'system' => 'secondary',
            'other' => 'light',
        ];
    }
}
