<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 物流公司模型
 * 统一管理物流公司信息和各平台物流编码映射
 */
class LogisticsCompany extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'platform_codes', 'channel_config',
        'first_weight', 'additional_weight', 'is_international', 'status',
    ];

    protected function casts(): array
    {
        return [
            'platform_codes' => 'array',
            'channel_config' => 'array',
            'is_international' => 'boolean',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取指定平台的物流编码
     */
    public function getPlatformCode(string $platform): ?string
    {
        return $this->platform_codes[$platform] ?? null;
    }
}