<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 审批流定义（TC-11）。
 *
 * 一条流 = 模块 + 单据类型（document_type）+ 有序步骤（approval_steps）。
 * ERP 的「销售订单/采购订单审批流」与 SOHO 的 8 条 FLOW-* 都落在这里：
 * SOHO 原来把节点塞在 flow_config JSON 里，迁入时摊平成 approval_steps（语义不变）。
 */
class ApprovalFlow extends Model
{
    protected $fillable = ['module', 'name', 'document_type', 'description', 'enabled', 'created_by'];

    protected $casts = ['enabled' => 'boolean'];

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'approval_flow_id')->orderBy('step_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(ApprovalInstance::class, 'approval_flow_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForModule($q, string $module)
    {
        return $q->where('module', $module);
    }

    public function scopeEnabled($q)
    {
        return $q->where('enabled', true);
    }
}
