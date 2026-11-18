<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * IP 白名单模型
 * 管理 IP 访问控制规则
 */
class IpWhitelist extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;

    protected $fillable = [
        'ip_address',
        'description',
        'type',
        'scope',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * 关联创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 类型标签
     */
    public static function getTypeLabels(): array
    {
        return [
            'allow' => '白名单',
            'block' => '黑名单',
        ];
    }

    /**
     * 类型颜色
     */
    public static function getTypeColors(): array
    {
        return [
            'allow' => 'success',
            'block' => 'danger',
        ];
    }

    /**
     * 适用范围标签
     */
    public static function getScopeLabels(): array
    {
        return [
            'all' => '全部',
            'admin' => '管理后台',
            'api' => 'API接口',
        ];
    }

    /**
     * 作用域：仅白名单
     */
    public function scopeAllow($query)
    {
        return $query->where('type', 'allow');
    }

    /**
     * 作用域：仅黑名单
     */
    public function scopeBlock($query)
    {
        return $query->where('type', 'block');
    }

    /**
     * 作用域：已启用
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 检查指定 IP 是否在白名单中
     */
    public static function isAllowed(string $ip, string $scope = 'all'): bool
    {
        // 获取适用的规则
        $rules = self::where('is_active', true)
            ->where('scope', $scope)
            ->orWhere('scope', 'all')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($ip)) {
                return $rule->type === 'allow';
            }
        }

        // 默认允许
        return true;
    }

    /**
     * 检查 IP 是否匹配此规则
     */
    public function matches(string $ip): bool
    {
        // 支持 CIDR 格式
        if (str_contains($this->ip_address, '/')) {
            return $this->ipInRange($ip, $this->ip_address);
        }

        // 单 IP 匹配
        return $ip === $this->ip_address;
    }

    /**
     * 检查 IP 是否在网段内
     */
    private function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        $subnetLong &= $mask;
        return ($ipLong & $mask) === $subnetLong;
    }
}