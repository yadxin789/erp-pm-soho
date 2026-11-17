<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Model;

class ApprovalTask extends Model
{
    protected $table = 'erp_approval_tasks';

    protected $fillable = [
        'document_type', 'document_id', 'step_order', 'step_name',
        'approver_id', 'status', 'comment', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(\App\Core\Models\User::class, 'approver_id');
    }
}
