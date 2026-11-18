<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * 文件存储配置模型
 * 管理系统文件存储磁盘的配置信息
 * 敏感字段（access_key、secret_key）进行加密存储
 */
class FileStorageConfig extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'disk_name', 'name', 'driver', 'config',
        'domain', 'is_default', 'is_active', 'last_modified_by',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 最后修改人关联
     */
    public function lastModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * 仅启用中的作用域
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 驱动类型标签
     */
    public static function getDriverLabels(): array
    {
        return [
            'local' => '本地存储',
            'oss' => '阿里云OSS',
            'cos' => '腾讯云COS',
            'qiniu' => '七牛云',
            's3' => 'AWS S3',
        ];
    }

    /**
     * 驱动图标
     */
    public static function getDriverIcons(): array
    {
        return [
            'local' => 'fa-hdd',
            'oss' => 'fa-cloud',
            'cos' => 'fa-cloud',
            'qiniu' => 'fa-cloud',
            's3' => 'fa-cloud',
        ];
    }

    /**
     * 驱动颜色
     */
    public static function getDriverColors(): array
    {
        return [
            'local' => 'secondary',
            'oss' => 'warning',
            'cos' => 'info',
            'qiniu' => 'success',
            's3' => 'primary',
        ];
    }

    /**
     * 加密敏感配置字段
     * 将 access_key / secret_key 加密后存入 config
     */
    public static function encryptSecrets(array $config): array
    {
        // 敏感字段列表
        $sensitiveKeys = ['access_key', 'secret_key', 'access_secret', 'secret'];

        foreach ($sensitiveKeys as $key) {
            if (!empty($config[$key])) {
                // 使用 Laravel 加密器加密敏感字段
                $config[$key] = Crypt::encryptString($config[$key]);
            }
        }

        return $config;
    }

    /**
     * 解密敏感配置字段
     */
    public function decryptSecrets(): array
    {
        $config = $this->config ?? [];

        $sensitiveKeys = ['access_key', 'secret_key', 'access_secret', 'secret'];

        foreach ($sensitiveKeys as $key) {
            if (!empty($config[$key])) {
                try {
                    // 解密敏感字段，失败则返回原值
                    $config[$key] = Crypt::decryptString($config[$key]);
                } catch (\Exception $e) {
                    // 解密失败保持原值
                }
            }
        }

        return $config;
    }

    /**
     * 获取脱敏后的配置（用于展示，敏感字段用星号替代）
     */
    public function getMaskedConfig(): array
    {
        $config = $this->config ?? [];

        $sensitiveKeys = ['access_key', 'secret_key', 'access_secret', 'secret'];

        foreach ($sensitiveKeys as $key) {
            if (!empty($config[$key])) {
                // 脱敏处理：只显示前4位和后2位
                $value = $config[$key];
                $config[$key] = str_repeat('*', max(strlen($value) - 6, 0)) . substr($value, -2);
            }
        }

        return $config;
    }
}
