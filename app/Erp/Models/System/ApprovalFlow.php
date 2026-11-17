<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    protected $table = 'erp_approval_flows';

    protected $fillable = ['name', 'document_type', 'enabled'];

    public function steps()
    {
        return $this->hasMany(ApprovalFlowStep::class)->orderBy('step_order');
    }
}
