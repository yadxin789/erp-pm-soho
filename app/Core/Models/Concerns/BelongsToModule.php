<?php

namespace App\Core\Models\Concerns;

use App\Core\Support\ModuleContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 模块归属 trait（彻底合并核心，见 P0 D20）
 *
 * 用法：Core 的共享 Model 里 use BelongsToModule;
 *  - 读取：自动追加 where(module = 当前模块上下文)，模块之间互不可见
 *  - 写入：creating 时自动补 module（上下文为空则不填，交给调用方）
 *  - 跨模块查询：ModuleContext::clear() 或直接 withoutGlobalScope('module')
 *
 * 注意：model 表必须有 `module` 列（见 01-目标数据库字典）。
 */
trait BelongsToModule
{
    public static function bootBelongsToModule(): void
    {
        static::addGlobalScope('module', function (Builder $builder) {
            if (! ModuleContext::hasScope()) {
                return; // 无上下文（Core 后台 / CLI 未指定）→ 不限制
            }

            $builder->where(
                $builder->getModel()->getTable().'.module',
                ModuleContext::current()
            );
        });

        static::creating(function (Model $model) {
            if (! isset($model->module) || $model->module === null || $model->module === '') {
                if (ModuleContext::hasScope()) {
                    $model->module = ModuleContext::current();
                }
            }
        });
    }

    /** 显式按模块查询（跨模块报表用） */
    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->withoutGlobalScope('module')->where($this->getTable().'.module', $module);
    }

    /** 显式跨模块查询 */
    public function scopeAllModules(Builder $query): Builder
    {
        return $query->withoutGlobalScope('module');
    }
}
