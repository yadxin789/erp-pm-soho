<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户模型
 * 存储电商平台客户的基本信息和消费数据
 */
class Customer extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'group_id',
        'name',
        'email',
        'phone',
        'password',
        'status',
        'total_spent',
        'order_count',
        'last_order_at',
        'notes',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_order_at' => 'datetime',
            'status' => 'boolean',
            'total_spent' => 'decimal:2',
            'order_count' => 'integer',
        ];
    }

    /**
     * 获取所属客户分组
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }
}