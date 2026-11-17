<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/** 个人待办 */
class ProjectTodo extends Model
{
    protected $table = 'erp_project_todos';

    protected $fillable = ['user_id', 'project_id', 'type', 'ref_id', 'title', 'due_date', 'done_at'];

    protected $casts = ['due_date' => 'date', 'done_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
