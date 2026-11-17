<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 跨模块例外授权：用户在 users.module 之外临时进入别的模块（D15 第二级）。
 */
class UserModuleAccess extends Model
{
    protected $table = 'user_module_access';

    protected $fillable = [
        'user_id',
        'module',
        'granted_by',
        'reason',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
