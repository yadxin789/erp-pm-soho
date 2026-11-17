<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'erp_audit_logs';

    use HasFactory;

    protected $fillable = [
        'action', 'module', 'user_id', 'user_name', 'record_id', 'record_type',
        'old_values', 'new_values', 'remark', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Core\Models\User::class);
    }
}
