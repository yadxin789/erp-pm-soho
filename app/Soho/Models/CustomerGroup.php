<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户分组模型
 * 用于对客户进行分类管理，如VIP客户、普通客户等
 */
class CustomerGroup extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'color',
        'sort',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    /**
     * 获取该分组下的所有客户
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'group_id');
    }
}