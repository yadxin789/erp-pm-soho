<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 店铺模型
 * Token等敏感凭证统一存储在ShopCredential模型中，通过关联获取
 */
class Shop extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use SoftDeletes, LogsActivity, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform', 'shop_name', 'shop_code', 'shop_url',
        'app_key', 'app_secret', 'auth_status', 'status', 'sync_enabled',
        'remark', 'platform_config',
    ];

    protected function casts(): array
    {
        return [
            'auth_status' => 'integer',
            'status' => 'boolean',
            'sync_enabled' => 'boolean',
            'platform_config' => 'array',
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
     * 获取店铺凭证（Token统一存储在此）
     */
    public function credential(): HasOne
    {
        return $this->hasOne(ShopCredential::class);
    }

    /**
     * 获取平台SKU列表
     */
    public function platformSkus(): HasMany
    {
        return $this->hasMany(PlatformSku::class);
    }

    /**
     * 获取平台订单列表
     */
    public function platformOrders(): HasMany
    {
        return $this->hasMany(PlatformOrder::class);
    }

    /**
     * 获取受限用户列表
     */
    public function limitedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'soho_user_shop_limit')
            ->withTimestamps();
    }

    /**
     * 获取售后记录列表
     */
    public function afterSales(): HasMany
    {
        return $this->hasMany(OrderAfterSale::class);
    }

    /**
     * 获取API日志列表
     */
    public function apiLogs(): HasMany
    {
        return $this->hasMany(PlatformApiLog::class);
    }

    /**
     * 获取平台中文标签
     */
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            'taobao'    => '淘宝',
            'jd'        => '京东',
            'pinduoduo' => '拼多多',
            'amazon'    => '亚马逊',
            default     => $this->platform,
        };
    }

    /**
     * 访问器：店铺名称（映射 shop_name）
     */
    public function getNameAttribute(): string
    {
        return $this->shop_name;
    }

    /**
     * 获取解密后的Access Token（从ShopCredential获取）
     * 使用懒加载避免N+1查询，通过exists判断减少不必要的查询
     */
    public function getAccessTokenAttribute(): ?string
    {
        if (!$this->relationLoaded('credential') && $this->credential()->exists()) {
            $this->load('credential');
        }
        return $this->credential?->access_token;
    }

    /**
     * 获取解密后的Refresh Token（从ShopCredential获取）
     */
    public function getRefreshTokenAttribute(): ?string
    {
        if (!$this->relationLoaded('credential') && $this->credential()->exists()) {
            $this->load('credential');
        }
        return $this->credential?->refresh_token;
    }

    /**
     * 获取Token过期时间（从ShopCredential获取）
     */
    public function getTokenExpiresAtAttribute(): ?\Carbon\Carbon
    {
        if (!$this->relationLoaded('credential') && $this->credential()->exists()) {
            $this->load('credential');
        }
        return $this->credential?->token_expires_at;
    }

    /**
     * 判断Token是否过期
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * 更新店铺凭证（Token）
     *
     * @param array $tokenData Token数据，包含access_token, refresh_token, expires_in等
     * @return ShopCredential
     */
    public function updateCredentials(array $tokenData): ShopCredential
    {
        return $this->credential()->updateOrCreate([], [
            'access_token_encrypted' => $tokenData['access_token'],
            'refresh_token_encrypted' => $tokenData['refresh_token'] ?? $this->refresh_token,
            'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 86400),
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'last_refresh_at' => now(),
        ]);
    }
}
