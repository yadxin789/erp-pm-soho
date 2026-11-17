<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 项目组群聊消息 */
class ProjectMessage extends Model
{
    protected $table = 'project_messages';

    use SoftDeletes;

    protected $fillable = [
        'project_id', 'user_id', 'content', 'file_name', 'file_path', 'file_size', 'file_mime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function getHasFileAttribute(): bool
    {
        return (bool) $this->file_path;
    }
}
