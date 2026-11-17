<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 任务交付文件
 *
 * 状态：pending 待审核 / approved 已通过 / rejected 已驳回
 */
class TaskFile extends Model
{
    use SoftDeletes;

    public const STATUS = [
        'pending' => '待审核',
        'approved' => '已通过',
        'rejected' => '已驳回',
    ];

    protected $table = 'files';

    protected $fillable = [
        'task_id', 'project_id', 'original_name', 'stored_path', 'mime_type', 'size',
        'version', 'status', 'uploaded_by', 'audited_by', 'audited_at', 'audit_remark', 'deleted_by',
    ];

    protected $casts = [
        'audited_at' => 'datetime',
        'size' => 'integer',
        'version' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class, 'file_id')->orderByDesc('version');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? (string) $this->status;
    }

    public function getSizeLabelAttribute(): string
    {
        $size = (int) $this->size;
        if ($size >= 1048576) {
            return round($size / 1048576, 1) . ' MB';
        }
        if ($size >= 1024) {
            return round($size / 1024, 1) . ' KB';
        }

        return $size . ' B';
    }
}
