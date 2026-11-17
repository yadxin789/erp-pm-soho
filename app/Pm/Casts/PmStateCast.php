<?php

namespace App\Pm\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;
use Spatie\ModelStates\State;

/**
 * B-03：共享表兼容投射器。
 *
 * PM 原库把状态存成 State 类的全名（App\States\Project\InProgress）；合并后
 * projects/tasks 是共享表，ERP 侧仍用 snake_case 字符串（in_progress/pending）。
 * 该 Cast 两者都认：读取时先按全类名解析，再按 snake_case 短名回退；
 * 写入时统一写 State 全类名（与 PM 原库口径一致，保证 where('status', Xxx::class) 命中）。
 */
class PmStateCast implements CastsAttributes
{
    public function __construct(protected string $baseStateClass)
    {
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $class = $this->resolveClass($value);

        $state = new $class($model);
        $state->setField($key);

        return $state;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof State) {
            return Str::snake(class_basename($value));
        }

        return Str::snake(class_basename($this->resolveClass($value)));
    }

    /** 全类名或 snake 短名 → State 全类名 */
    protected function resolveClass(string $value): string
    {
        if (class_exists($value) && is_subclass_of($value, $this->baseStateClass)) {
            return $value;
        }

        $mapping = $this->baseStateClass::getStateMapping();

        if ($mapping->has($value)) {
            return $mapping->get($value);
        }

        foreach ($mapping as $class) {
            $short = Str::snake(class_basename($class));
            if ($short === $value || class_basename($class) === $value) {
                return $class;
            }
        }

        return $mapping->first() ?? $value;
    }
}
