<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 知识库 */
class KnowledgeBase extends Model
{
    protected $table = 'erp_knowledge_bases';

    use SoftDeletes;

    protected $fillable = ['title', 'description', 'department_id', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function docs()
    {
        return $this->hasMany(KnowledgeDoc::class, 'knowledge_id')->orderByDesc('updated_at');
    }
}
