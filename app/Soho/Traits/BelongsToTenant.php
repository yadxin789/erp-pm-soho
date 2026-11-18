<?php

namespace App\Soho\Traits;

use App\Soho\Scopes\TenantScope;

/**
 * 租户归属Trait
 * 为模型添加自动租户过滤和自动填充 tenant_id
 */
trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait
     */
    public static function bootBelongsToTenant(): void
    {
        // 自动添加租户全局作用域
        static::addGlobalScope(new TenantScope());

        // 创建时自动填充 tenant_id
        static::creating(function ($model) {
            if (is_null($model->tenant_id)) {
                $model->tenant_id = auth()->user()?->tenant_id ?? 1;
            }
        });
    }

    /**
     * 获取无租户过滤的查询构建器
     */
    public static function withoutTenantScope(): \Illuminate\Database\Eloquent\Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
