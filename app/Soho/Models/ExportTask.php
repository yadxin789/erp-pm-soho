<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 导出任务模型
 * 记录数据导出任务的执行状态、文件路径、行数等信息，支持异步导出和文件过期清理
 */
class ExportTask extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'task_no', 'task_type', 'params', 'status',
        'file_path', 'file_size', 'row_count', 'operator_id',
        'created_at', 'completed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'file_size' => 'integer',
            'row_count' => 'integer',
            'created_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}