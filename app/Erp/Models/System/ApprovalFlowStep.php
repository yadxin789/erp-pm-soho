<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * ERP 审批流步骤（P2-4 归一）——物理表统一到 Core `approval_steps`。
 */
class ApprovalFlowStep extends Model
{
    protected $table = 'approval_steps';

    protected $fillable = ['approval_flow_id', 'step_order', 'name', 'approver_type', 'approver_value'];

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }
}
