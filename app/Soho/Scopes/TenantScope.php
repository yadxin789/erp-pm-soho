<?php

namespace App\Soho\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * 租户全局作用域
 * 自动按当前登录用户的 tenant_id 过滤查询结果
 */
class TenantScope implements Scope
{
    /**
     * 应用租户过滤条件
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->getCurrentTenantId();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * 扩展查询方法：允许无租户过滤的查询
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        $builder->macro('withAllTenants', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });
    }

    /**
     * 获取当前租户ID
     */
    protected function getCurrentTenantId(): ?int
    {
        // 超级管理员可查看所有租户数据
        if (Auth::user()?->hasRole('super_admin')) {
            return null;
        }

        return Auth::user()?->tenant_id;
    }
}
