<?php

use App\Core\Support\ModuleContext;

if (! function_exists('module_asset')) {
    /**
     * 按模块取 public 资产 URL（T0-11）。
     * 原项目 asset('css/app.css') → module_asset('css/app.css')，落到 /assets/{module}/css/app.css。
     * 未指定模块时用当前 ModuleContext，再退回 core。
     */
    function module_asset(string $path, ?string $module = null): string
    {
        $module = $module ?: (ModuleContext::current() ?: config('modules.default', 'core'));
        $base = config("modules.modules.{$module}.assets", 'assets/core');

        return asset($base.'/'.ltrim($path, '/'));
    }
}

if (! function_exists('module_config')) {
    /** 取模块注册表里的字段（route_prefix / table_prefix / view / permission / assets） */
    function module_config(string $module, string $key, mixed $default = null): mixed
    {
        return config("modules.modules.{$module}.{$key}", $default);
    }
}

if (! function_exists('permission_candidates')) {
    /**
     * 权限名候选：兼容「裸名（inventory.view）」与「模块前缀名（soho.inventory.view）」两套命名。
     * 原三项目分别引入权限，SOHO 角色实际持有 `soho.*`，ERP/PM 部分沿用裸名，统一在此兼容。
     */
    function permission_candidates(string $permission): array
    {
        $candidates = [$permission];

        $module = ModuleContext::current() ?: (request()->segment(1) ?: null);
        if ($module && ! str_starts_with($permission, $module.'.')) {
            $candidates[] = $module.'.'.$permission;
        }
        if (! str_starts_with($permission, 'soho.')) {
            $candidates[] = 'soho.'.$permission;
        }

        return array_values(array_unique($candidates));
    }
}

if (! function_exists('can_perm')) {
    /** 视图/控制器用：任一候选权限命中即视为有权（super_admin 直接放行） */
    function can_perm(string|array $permissions): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        $candidates = [];
        foreach ((array) $permissions as $p) {
            $candidates = array_merge($candidates, permission_candidates($p));
        }

        // 注意：Gate::check(array) 要求全部通过，这里必须逐个判定（任一命中即有权）
        foreach (array_unique($candidates) as $candidate) {
            if ($user->can($candidate)) {
                return true;
            }
        }

        return false;
    }
}