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

    /** 跨模块共享部门（公司级单一组织行，归口 ERP，各模块可见可挂人） */
    public const SHARED_CODES = ['GM', 'WH', 'FIN', 'HR'];

    /** 某模块可见的部门集合：本模块部门 + 公司级共享部门 */
    public function scopeVisibleTo($query, string $module)
    {
        return $query->where(function ($q) use ($module) {
            $q->where('module', $module)->orWhereIn('code', self::SHARED_CODES);
        });
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
