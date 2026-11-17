<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 统一文件（TC-12）：三源的任务附件、部门资料、SOHO 商品图/凭证都落这一张表。
 *
 * 与 PM 原 File 的差异：多了 module / department_id / category / visibility / disk；
 * PM 的 task_id、uploader_id、auditor_id、审核状态原样保留（迁入 PM 项目域时直接可用）。
 * 软删除用 deleted_at + deleted_by：删的人记下来，个人回收站靠它筛。
 */
class File extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const VISIBILITY_MODULE = 'module';
    public const VISIBILITY_PRIVATE = 'private';

    protected $fillable = [
        'module', 'department_id', 'task_id', 'uploader_id', 'auditor_id',
        'name', 'original_name', 'disk', 'path', 'size', 'mime_type',
        'category', 'visibility', 'version', 'status',
        'audit_time', 'audit_reason', 'deleted_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
        'audit_time' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class)->orderByDesc('version');
    }

    public function scopeForModule($q, string $module)
    {
        return $q->where('module', $module);
    }

    public function extension(): string
    {
        return strtolower(pathinfo((string) ($this->original_name ?: $this->path), PATHINFO_EXTENSION));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => '已审核',
            self::STATUS_REJECTED => '已驳回',
            default => '待审核',
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'ok',
            self::STATUS_REJECTED => 'danger',
            default => 'warn',
        };
    }
}
