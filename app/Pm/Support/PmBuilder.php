<?php

namespace App\Pm\Support;

use App\Pm\Models\Project;
use App\Pm\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * B-03：共享表状态查询兼容 Builder。
 *
 * PM 原代码大量写 where('status', \App\Pm\States\Project\InProgress::class)，
 * 而合并后 projects/tasks 是共享表，ERP 侧存 snake_case 短名（in_progress）。
 * 这里在查询入口把 State 全类名/字符串统一翻译成 snake_case 短名，
 * 使 PM 原查询与 ERP 数据口径同时成立。
 */
class PmBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'status') {
            [$operator, $value] = $this->normalizeStatus($operator, $value);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if ($column === 'status' && is_array($values)) {
            $values = array_map(fn ($v) => $this->statusToShort($v), $values);
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    /** where('status', X) 两参写法归一 */
    protected function normalizeStatus($operator, $value): array
    {
        if ($value === null && is_string($operator)) {
            return ['=', $this->statusToShort($operator)];
        }

        return [$operator, $this->statusToShort($value)];
    }

    protected function statusToShort($value)
    {
        if (! is_string($value) || ! str_contains($value, 'States\\')) {
            return $value;
        }

        if (! class_exists($value)) {
            return $value;
        }

        $base = $this->model instanceof Project
            ? \App\Pm\States\Project\ProjectState::class
            : \App\Pm\States\Task\TaskState::class;

        if (! is_subclass_of($value, $base)) {
            return $value;
        }

        return Str::snake(class_basename($value));
    }
}
