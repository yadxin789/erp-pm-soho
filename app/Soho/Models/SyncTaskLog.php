<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncTaskLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_sync_task_logs';

    protected $fillable = [
        'task_name', 'task_type', 'platform', 'shop_id', 'status',
        'total_count', 'success_count', 'fail_count', 'error_message',
        'extra_data', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'extra_data'  => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'running' => '运行中',
            'success' => '成功',
            'failed'  => '失败',
            default   => $this->status,
        };
    }
}
