<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/** 任务延期申请 */
class ExtensionApplication extends Model
{
    protected $table = 'erp_extension_applications';

    public const STATUS = [
        'applying' => '待审批',
        'approved' => '已批准',
        'rejected' => '已驳回',
    ];

    protected $fillable = [
        'task_id', 'project_id', 'applicant_id', 'days', 'reason', 'status', 'approver_id', 'approved_at', 'remark',
    ];

    protected $casts = ['approved_at' => 'datetime', 'days' => 'integer'];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? (string) $this->status;
    }
}
