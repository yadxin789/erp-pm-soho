<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 岗位（erp 口径）：pm/soho 为空表起步。
 * 注意：岗位 ≠ 角色，角色走 spatie 的 model_has_roles。
 */
class Position extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'department_id',
        'level',
        'headcount',
        'default_role',
        'sort_order',
        'active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'sort_order' => 'integer',
        'headcount'  => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
