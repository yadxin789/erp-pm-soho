<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 平台状态映射模型
 * 维护各平台状态到统一状态的映射关系
 */
class PlatformStatusMapping extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'platform', 'object_type', 'platform_status', 'unified_status', 'description',
    ];

    /**
     * 根据平台和原始状态获取统一状态
     */
    public static function getUnifiedStatus(string $platform, string $objectType, string $platformStatus): ?string
    {
        $mapping = static::where('platform', $platform)
            ->where('object_type', $objectType)
            ->where('platform_status', $platformStatus)
            ->first();

        return $mapping?->unified_status;
    }
}