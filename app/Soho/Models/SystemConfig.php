<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_system_configs';

    protected $fillable = [
        'key', 'value', 'group', 'type', 'label', 'remark', 'sort_order',
    ];

    public static function getValue(string $key, $default = null): mixed
    {
        $config = static::where('key', $key)->first();

        if (!$config) {
            return $default;
        }

        return match($config->type) {
            'int'  => (int) $config->value,
            'bool' => (bool) $config->value,
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    public static function setValue(string $key, mixed $value, string $type = 'string'): void
    {
        if (is_bool($value)) {
            $type = 'bool';
            $value = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $type = 'json';
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}
