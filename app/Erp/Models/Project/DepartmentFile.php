<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 部门资料文件 */
class DepartmentFile extends Model
{
    protected $table = 'erp_department_files';

    use SoftDeletes;

    protected $fillable = [
        'department_id', 'original_name', 'stored_path', 'mime_type', 'size', 'version', 'uploaded_by', 'status',
    ];

    protected $casts = ['size' => 'integer', 'version' => 'integer'];

    public function department()
    {
        return $this->belongsTo(\App\Core\Models\Department::class, 'department_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
