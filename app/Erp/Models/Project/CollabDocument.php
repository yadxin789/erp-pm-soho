<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 协同办公文档（OnlyOffice 编辑对象） */
class CollabDocument extends Model
{
    protected $table = 'collab_documents';

    use SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'doc_key', 'file_path', 'ext', 'created_by', 'updated_by', 'last_saved_at',
    ];

    protected $casts = ['last_saved_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** OnlyOffice 文件类型（documentType）：word / cell / slide */
    public function getDocTypeAttribute(): string
    {
        return match ($this->ext) {
            'xlsx', 'xls', 'csv' => 'cell',
            'pptx', 'ppt' => 'slide',
            default => 'word',
        };
    }
}
