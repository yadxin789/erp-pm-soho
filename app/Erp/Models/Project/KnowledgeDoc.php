<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 知识库文档 */
class KnowledgeDoc extends Model
{
    protected $table = 'erp_knowledge_docs';

    use SoftDeletes;

    protected $fillable = ['knowledge_id', 'title', 'content', 'version', 'created_by'];

    protected $casts = ['version' => 'integer'];

    public function knowledge()
    {
        return $this->belongsTo(KnowledgeBase::class, 'knowledge_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
