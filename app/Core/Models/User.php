<?php

namespace App\Core\Models;

use App\Core\Support\ModuleContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Core 唯一用户模型：三模块共用（D20 彻底合并：不保留模块私有 User）。
 *
 * 表列见 database/migrations/0001_01_01_000000_create_users_table.php 与
 * 01-目标数据库字典 §2.1（27 列并集）。
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'real_name',
        'email',
        'email_verified_at',
        'phone',
        'password',
        'avatar',
        'signature',
        'department_id',
        'position_id',
        'status',
        'is_admin',
        'tenant_id',
        'module',
        'last_login_at',
        'last_login_ip',
        'last_seen_at',
        'wecom_userid',
        'wecom_bound_at',
        'wecom_notify_enabled',
        'wecom_notify_settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'last_login_at'        => 'datetime',
        'last_seen_at'         => 'datetime',
        'wecom_bound_at'       => 'datetime',
        'wecom_notify_enabled' => 'boolean',
        'wecom_notify_settings' => 'array',
        'status'               => 'integer',
        'is_admin'             => 'boolean',
    ];

    /** 部门关系（module 归属的第三级来源） */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /** 跨模块例外授权（D15 的第二级来源） */
    public function moduleAccess()
    {
        return $this->hasMany(UserModuleAccess::class);
    }

    /** 头像地址兜底：无头像时用姓名首字，视图侧不再判断 */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ?: null;
    }

    /** 展示用名称：真实姓名 → name → username → 邮箱前缀（三源字段各不相同，视图统一调这个） */
    public function displayName(): string
    {
        return $this->real_name
            ?: ($this->name
            ?: ($this->username
            ?: (strstr((string) $this->email, '@', true) ?: '用户')));
    }

    /**
     * 解析该用户可进入的模块清单（D15 准入链）。
     *
     * 顺序：users.module → user_module_access（未过期）→ departments.module → core
     * 返回值始终包含 'core'（核心后台是所有登录用户的兜底去处）。
     *
     * @return array<int,string>
     */
    public function resolveModules(): array
    {
        $modules = [];

        if ($this->module) {
            $modules[] = $this->module;
        }

        foreach ($this->moduleAccess as $access) {
            if ($access->expires_at === null || $access->expires_at->isFuture()) {
                $modules[] = $access->module;
            }
        }

        if (! $modules && $this->department && $this->department->module) {
            $modules[] = $this->department->module;
        }

        $modules = array_values(array_unique(array_filter($modules, fn ($m) => ModuleContext::exists($m))));

        return array_values(array_unique(array_merge($modules, ['core'])));
    }

    /** 除 core 兜底外，是否拥有某个业务模块 */
    public function canEnterModule(string $module): bool
    {
        return in_array($module, $this->resolveModules(), true);
    }
}
