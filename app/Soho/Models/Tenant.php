<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 租户模型
 * 表示一个企业主体，支持多租户隔离
 */
class Tenant extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'name', 'code', 'status', 'logo', 'contact_name',
        'contact_phone', 'email', 'address', 'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    /**
     * 获取租户下的所有店铺
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * 获取租户下的所有用户
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * 获取租户下的所有仓库
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}