<?php

namespace App\Soho\Models;

/**
 * SOHO 模块用户：与 Core 共用 users 表与 Spatie 角色表。
 *
 * Spatie 以 morph class 定位 model_has_roles/permissions 行，
 * 历史数据写入的 morph type 为 App\Core\Models\User，
 * 因此这里固定返回同一 morph class，避免 SOHO 侧 roles 关系查不到角色。
 */
class User extends \App\Core\Models\User
{
    public function getMorphClass(): string
    {
        return \App\Core\Models\User::class;
    }
}
