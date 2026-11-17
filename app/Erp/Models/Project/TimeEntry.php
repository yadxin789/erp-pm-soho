<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $table = 'erp_time_entries';

    protected $fillable = [
        'project_task_id', 'user_id', 'date', 'hours', 'description',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
