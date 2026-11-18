<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * 系统参数模型
 * 管理外部平台连接所需的API Key、密钥、回调地址等配置参数
 * 敏感信息（如API密钥）支持加密存储
 */
class SystemParameter extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'param_key', 'param_name', 'param_value',
        'category', 'value_type', 'description',
        'is_encrypted', 'is_active', 'last_modified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 关联租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 关联最后修改人
     */
    public function lastModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * 获取参数值（自动解密）
     * 如果参数标记为加密，则从存储的密文中解密返回
     */
    public function getParamValueAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        // 如果标记为加密，尝试解密
        if ($this->is_encrypted) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                // 解密失败返回原文（可能是旧数据未加密）
                return $value;
            }
        }

        return $value;
    }

    /**
     * 设置参数值（自动加密）
     * 如果参数标记为加密，则在存储前自动加密
     */
    public function setParamValueAttribute($value): void
    {
        if ($value !== null && $this->is_encrypted) {
            $this->attributes['param_value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['param_value'] = $value;
        }
    }

    /**
     * 作用域：仅查询启用的参数
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 作用域：按分类筛选
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 获取脱敏显示值（用于列表展示，隐藏敏感信息）
     * 加密参数只显示前4位和后4位，中间用星号替代
     */
    public function getMaskedValueAttribute(): string
    {
        $value = $this->param_value;

        if (!$value) {
            return '-';
        }

        // 非加密参数直接显示
        if (!$this->is_encrypted) {
            return $value;
        }

        // 加密参数脱敏显示
        $length = strlen($value);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 4) . str_repeat('*', $length - 8) . substr($value, -4);
    }

    /**
     * 分类标签映射
     */
    public static function getCategoryLabels(): array
    {
        return [
            'platform_api' => '平台API',
            'callback_url' => '回调地址',
            'secret_key' => '密钥',
            'other' => '其他',
        ];
    }

    /**
     * 参数类型标签映射
     */
    public static function getValueTypeLabels(): array
    {
        return [
            'string' => '字符串',
            'boolean' => '布尔',
            'integer' => '整数',
            'json' => 'JSON',
        ];
    }
}
