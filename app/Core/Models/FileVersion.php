<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 文件版本快照：每次「上传新版本」或 OnlyOffice 保存回调，都把旧文件拷一份到这里。
 *
 * snapshot 存的是快照相对路径；不软删（随主文件一起被彻底删除时清掉）。
 */
class FileVersion extends Model
{
    protected $fillable = [
        'file_id', 'task_id', 'version', 'disk', 'path',
        'uploader_id', 'auditor_id', 'status', 'audit_time', 'audit_reason', 'snapshot',
    ];

    protected $casts = ['version' => 'integer', 'audit_time' => 'datetime', 'snapshot' => 'array'];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
