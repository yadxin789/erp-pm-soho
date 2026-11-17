<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 项目模板（预置任务清单，创建项目时可套用） */
class ProjectTemplate extends Model
{
    protected $table = 'erp_project_templates';

    use SoftDeletes;

    protected $fillable = ['name', 'description', 'config', 'created_by'];

    protected $casts = ['config' => 'array'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** config.tasks: [{name, priority, duration_days, task_type_id, department_id}] */
    public function getTasksAttribute(): array
    {
        return $this->config['tasks'] ?? [];
    }
}
