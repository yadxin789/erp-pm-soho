<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 公共资源文件 */
class ResourceFile extends Model
{
    protected $table = 'erp_resource_files';

    use SoftDeletes;

    protected $fillable = [
        'title', 'category', 'original_name', 'stored_path', 'mime_type', 'size', 'uploaded_by', 'downloads',
    ];

    protected $casts = ['size' => 'integer', 'downloads' => 'integer'];

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
