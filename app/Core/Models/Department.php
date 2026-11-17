<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 部门（三棵组织树合成一棵）：Core 唯一 Department 模型。
 * module 列表示该部门归属哪个业务模块，用户准入链的第三级来源（D15）。
 */
class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'description',
        'parent_id',
        'module',
        'type',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status'     => 'integer',
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /** 向上追溯模块归属：自身 module 为空时继承父级（01 字典 §2.2） */
    public function resolveModule(): ?string
    {
        $node = $this;
        $depth = 0;

        while ($node && $depth < 10) {
            if ($node->module) {
                return $node->module;
            }
            $node = $node->parent;
            $depth++;
        }

        return null;
    }
}
