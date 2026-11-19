<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * ERP 审批流配置（P2-4 归一）。
 *
 * 物理表统一到 Core `approval_flows`（`module='erp'`），与 SOHO 的 8 条 FLOW-* 同一张表；
 * 原 `erp_approval_flows` 已导入后保留为历史副本，不再写入。
 */
class ApprovalFlow extends Model
{
    protected $table = 'approval_flows';

    protected $fillable = ['module', 'name', 'document_type', 'description', 'enabled', 'created_by'];

    protected function casts(): array
    {
        return ['enabled' => 'bool'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('erp', fn (Builder $q) => $q->where('module', 'erp'));

        static::creating(function (self $model) {
            $model->module = 'erp';
            $model->created_by ??= auth()->id();
        });
    }

    public function steps()
    {
        return $this->hasMany(ApprovalFlowStep::class, 'approval_flow_id')->orderBy('step_order');
    }
}
