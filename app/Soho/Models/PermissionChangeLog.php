<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * 权限变更日志模型
 * 记录用户角色、权限的变更历史
 */
class PermissionChangeLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'change_type', 'target_user_id', 'operator_id', 'role_id',
        'permission_id', 'before_value', 'after_value', 'description',
        'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before_value' => 'array',
            'after_value' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * 关联目标用户
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * 关联操作人
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * 关联角色
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 关联权限
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * 变更类型标签
     */
    public static function getChangeTypeLabels(): array
    {
        return [
            'role_assign' => '分配角色',
            'role_remove' => '移除角色',
            'permission_assign' => '分配权限',
            'permission_remove' => '移除权限',
            'role_update' => '更新角色',
        ];
    }

    /**
     * 变更类型颜色
     */
    public static function getChangeTypeColors(): array
    {
        return [
            'role_assign' => 'success',
            'role_remove' => 'danger',
            'permission_assign' => 'primary',
            'permission_remove' => 'warning',
            'role_update' => 'info',
        ];
    }

    /**
     * 作用域：按目标用户筛选
     */
    public function scopeByTargetUser($query, int $userId)
    {
        return $query->where('target_user_id', $userId);
    }

    /**
     * 作用域：按操作人筛选
     */
    public function scopeByOperator($query, int $userId)
    {
        return $query->where('operator_id', $userId);
    }

    /**
     * 获取变更类型标签
     */
    public function getChangeTypeLabel(): string
    {
        return self::getChangeTypeLabels()[$this->change_type] ?? $this->change_type;
    }
}