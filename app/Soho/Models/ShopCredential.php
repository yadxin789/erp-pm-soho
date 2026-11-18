<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * 店铺凭证模型
 * 独立存储店铺的OAuth凭证，所有敏感信息均加密存储
 */
class ShopCredential extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $fillable = [
        'shop_id', 'client_id', 'client_secret_encrypted', 'access_token_encrypted',
        'refresh_token_encrypted', 'token_type', 'token_expires_at', 'refresh_expires_at',
        'scope', 'extra_data', 'authorized_at', 'last_refresh_at',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'token_expires_at' => 'datetime',
            'refresh_expires_at' => 'datetime',
            'authorized_at' => 'datetime',
            'last_refresh_at' => 'datetime',
        ];
    }

    /**
     * 获取所属店铺
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * 设置加密的客户端密钥
     */
    public function setClientSecretEncryptedAttribute(string $value): void
    {
        $this->attributes['client_secret_encrypted'] = Crypt::encryptString($value);
    }

    /**
     * 获取解密的客户端密钥
     */
    public function getClientSecretAttribute(): ?string
    {
        return $this->safeDecrypt($this->client_secret_encrypted);
    }

    /**
     * 设置加密的Access Token
     */
    public function setAccessTokenEncryptedAttribute(string $value): void
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($value);
    }

    /**
     * 获取解密的Access Token
     */
    public function getAccessTokenAttribute(): ?string
    {
        return $this->safeDecrypt($this->access_token_encrypted);
    }

    /**
     * 设置加密的Refresh Token
     */
    public function setRefreshTokenEncryptedAttribute(string $value): void
    {
        $this->attributes['refresh_token_encrypted'] = Crypt::encryptString($value);
    }

    /**
     * 获取解密的Refresh Token
     */
    public function getRefreshTokenAttribute(): ?string
    {
        return $this->safeDecrypt($this->refresh_token_encrypted);
    }


    /**
     * 迁入数据的密文来自参考项目（APP_KEY 不同），解密失败时返回 null，不让详情页 500。
     */
    protected function safeDecrypt(?string $cipher): ?string
    {
        if (! $cipher) {
            return null;
        }

        try {
            return Crypt::decryptString($cipher);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
