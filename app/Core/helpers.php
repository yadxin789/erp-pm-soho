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