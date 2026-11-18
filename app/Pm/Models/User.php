<?php

namespace App\Pm\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Pm\Traits\HasCustomNotifications;

class User extends Authenticatable
{
    use HasCustomNotifications, HasRoles, SoftDeletes;

    protected $fillable = [
        'username', 'password', 'name', 'avatar', 'bio',
        'department_id', 'role_id', 'is_active',
        'wecom_userid', 'wecom_bound_at', 'wecom_notify_enabled', 'wecom_notify_settings',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wecom_bound_at' => 'datetime',
            'wecom_notify_enabled' => 'boolean',
        ];
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 同步自定义 roles 表与 Spatie 权限角色的映射
     * 在用户保存后自动同步 Spatie 角色
     */
    protected static function booted()
    {
        static::saved(function (User $user) {
            // 同步自定义 role 到 Spatie 角色
            if ($user->role) {
                $spatieRoleName = $user->role->code;
                if (!$user->hasRole($spatieRoleName)) {
                    $user->syncRoles([$spatieRoleName]);
                }
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'creator_id');
    }

    public function executedTasks()
    {
        return $this->hasMany(Task::class, 'executor_id');
    }

    public function auditedTasks()
    {
        return $this->hasMany(Task::class, 'auditor_id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(File::class, 'uploader_id');
    }

    public function auditedFiles()
    {
        return $this->hasMany(File::class, 'auditor_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    /**
     * 使用 Spatie HasRoles trait 的角色检查方法（推荐）
     * 同时也保留原有方法以保持向后兼容
     */
    /*
     * 合并站的角色名统一加模块前缀（pm.admin / pm.project_creator ...），
     * 这里做名称映射，保持与参考项目 hasRole('admin') 等调用一致。
     */
    private const PM_ROLE_ALIASES = [
        'admin' => 'pm.admin',
        'project_creator' => 'pm.project_creator',
        'project_manager' => 'pm.project_manager',
        'task_executor' => 'pm.task_executor',
        'task_auditor' => 'pm.task_auditor',
        'support' => 'pm.support',
    ];

    private function hasPmRole(string $name): bool
    {
        return $this->hasRole(self::PM_ROLE_ALIASES[$name] ?? $name);
    }

    public function isAdmin()
    {
        return $this->hasPmRole('admin');
    }

    public function isProjectCreator()
    {
        return $this->hasPmRole('project_creator');
    }

    public function isProjectManager()
    {
        return $this->hasPmRole('project_manager');
    }

    public function isTaskExecutor()
    {
        return $this->hasPmRole('task_executor');
    }

    public function isTaskAuditor()
    {
        return $this->hasPmRole('task_auditor');
    }

    public function isSupport()
    {
        return $this->hasPmRole('support');
    }

    /**
     * 用户发送的消息（含群聊和私聊）
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
     * 用户收到的私聊消息
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}