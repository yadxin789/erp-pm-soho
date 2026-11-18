<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 敏感数据访问日志模型
 */
class SensitiveAccessLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'data_type', 'action', 'record_id',
        'record_identifier', 'ip_address', 'user_agent', 'accessed_at', 'remark',
    ];

    protected function casts(): array
    {
        return ['accessed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDataTypeLabels(): array
    {
        return [
            'customer' => '客户信息',
            'finance' => '财务数据',
            'order' => '订单数据',
            'product' => '商品数据',
        ];
    }

    public static function getActionLabels(): array
    {
        return [
            'view' => '查看',
            'export' => '导出',
            'edit' => '编辑',
            'delete' => '删除',
        ];
    }
}