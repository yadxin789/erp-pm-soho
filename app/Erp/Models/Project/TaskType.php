<?php

namespace App\Erp\Models\Project;

use Illuminate\Database\Eloquent\Model;

/**
 * 任务类型（按部门维护，创建项目时为每个任务选择类型）
 */
class TaskType extends Model
{
    protected $table = 'task_types';

    protected $fillable = ['name', 'description', 'department_id', 'is_required', 'sort_order'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(\App\Core\Models\Department::class, 'department_id');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'task_type_id');
    }
}
