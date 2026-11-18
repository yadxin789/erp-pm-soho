<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 平台账号模型
 * 存储平台开发者账号、卖家主体等信息，与Shop分离，用于多站点、多店铺的统一授权管理
 */
class PlatformAccount extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform_code', 'platform_name', 'account_type', 'account_name',
        'developer_id', 'app_secret_encrypted', 'seller_id', 'seller_name',
        'marketplace_id', 'marketplace_name', 'country_code', 'country_name',
        'oauth_status', 'access_token_encrypted', 'refresh_token_encrypted',
        'token_type', 'token_expires_at', 'refresh_expires_at', 'scope',
        'api_version', 'credential_version', 'lwa_client_id',
        'config', 'extra_data', 'authorized_at', 'last_token_refresh_at',
        'last_api_call_at', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'refresh_expires_at' => 'datetime',
            'config' => 'array',
            'extra_data' => 'array',
            'authorized_at' => 'datetime',
            'last_token_refresh_at' => 'datetime',
            'last_api_call_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(PlatformCapability::class, 'platform_account_id');
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'platform_account_id');
    }

    /**
     * 访问器：平台中文标签
     */
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform_code) {
            'taobao'    => '淘宝',
            'jd'        => '京东',
            'pinduoduo', 'pdd' => '拼多多',
            'amazon'    => '亚马逊',
            'douyin'    => '抖音',
            'shopify'   => 'Shopify',
            default     => $this->platform_name ?? $this->platform_code,
        };
    }

    /**
     * 访问器：OAuth 授权状态标签
     */
    public function getOauthStatusLabelAttribute(): string
    {
        return match($this->oauth_status) {
            'authorized'   => '已授权',
            'unauthorized' => '未授权',
            'expired'      => '已过期',
            'revoked'      => '已撤销',
            default        => '未授权',
        };
    }

    /**
     * 访问器：状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'    => '启用',
            'inactive'  => '禁用',
            'suspended' => '已暂停',
            default     => $this->status,
        };
    }

    /**
     * 访问器：解密后的 AppSecret
     */
    public function getAppSecretAttribute(): ?string
    {
        return $this->app_secret_encrypted ? decrypt($this->app_secret_encrypted) : null;
    }

    /**
     * 访问器：解密后的 AccessToken
     */
    public function getAccessTokenAttribute(): ?string
    {
        return $this->access_token_encrypted ? decrypt($this->access_token_encrypted) : null;
    }

    /**
     * 访问器：解密后的 RefreshToken
     */
    public function getRefreshTokenAttribute(): ?string
    {
        return $this->refresh_token_encrypted ? decrypt($this->refresh_token_encrypted) : null;
    }
}