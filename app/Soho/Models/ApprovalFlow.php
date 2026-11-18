<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 审批流模型
 * 定义审批规则和流程配置，支持多级审批、超时升级、金额阈值等
 */
class ApprovalFlow extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'type', 'scope', 'scope_condition',
        'trigger_condition', 'flow_config', 'escalation_config',
        'status', 'priority', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scope_condition' => 'array',
            'trigger_condition' => 'array',
            'flow_config' => 'array',
            'escalation_config' => 'array',
            'priority' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(ApprovalInstance::class, 'flow_id');
    }
}