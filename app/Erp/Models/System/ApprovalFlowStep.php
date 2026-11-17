<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    protected $table = 'erp_approval_flow_steps';

    protected $fillable = ['approval_flow_id', 'step_order', 'name', 'approver_type', 'approver_value'];

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }
}
