<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * 用户数据权限模型
 * 定义用户在各数据域（店铺、仓库、平台、部门等）上的操作权限和字段级权限
 */
class UserDataScope extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'role_id', 'subject_type', 'domain', 'action',
        'scope_strategy', 'scope_values', 'field_permissions', 'sensitivity_level',
        'can_export', 'export_limit', 'approval_threshold', 'price_change_limit',
        'stock_adjust_limit', 'effective_at', 'expires_at', 'status', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'scope_values' => 'array',
            'field_permissions' => 'array',
            'can_export' => 'boolean',
            'export_limit' => 'integer',
            'approval_threshold' => 'decimal:4',
            'price_change_limit' => 'decimal:4',
            'stock_adjust_limit' => 'decimal:4',
            'effective_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}