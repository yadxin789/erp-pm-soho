<?php

if (! function_exists('erp_route')) {
    /**
     * ERP 视图兼容路由解析。
     * 原 ERP 视图大量直接调用 route('app.xxx')，B-02 只迁入部分路由，
     * 缺失路由时返回 '#'，避免渲染阶段抛 UrlGenerationException 造成 500。
     * 路由迁入后自动切换为真实 URL（无需改视图）。
     */
    function erp_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $parameters, $absolute)
            : '#';
    }
}