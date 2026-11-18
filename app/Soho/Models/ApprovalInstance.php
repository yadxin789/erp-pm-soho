<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 审批实例模型
 * 记录一次完整的审批流程实例，包含审批节点、审批记录、业务数据快照等
 */
class ApprovalInstance extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'flow_id', 'type', 'biz_type', 'biz_id', 'biz_no',
        'title', 'content', 'biz_data', 'amount', 'currency',
        'current_node', 'nodes_snapshot', 'approval_logs', 'status',
        'submitted_by', 'submitted_at', 'started_at', 'completed_at',
        'deadline_at', 'approved_by', 'final_remark',
        'shop_id', 'warehouse_id',
    ];

    protected function casts(): array
    {
        return [
            'biz_data' => 'array',
            'nodes_snapshot' => 'array',
            'approval_logs' => 'array',
            'amount' => 'decimal:4',
            'current_node' => 'integer',
            'submitted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'deadline_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}