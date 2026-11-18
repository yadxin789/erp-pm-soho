<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 异常案例模型
 * 记录系统运行中产生的各类异常，包括API错误、同步失败、数据异常等，支持分配处理和状态跟踪
 */
class ExceptionCase extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'exception_code', 'severity', 'status',
        'shop_id', 'order_id', 'task_id',
        'error_message', 'stack_trace',
        'raw_request', 'raw_response',
        'handler_id', 'resolved_at', 'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'raw_request' => 'array',
            'raw_response' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'order_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }
}