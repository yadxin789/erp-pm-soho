<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('soho_route')) {
    /**
     * SOHO 菜单/视图路由解析：优先 soho.*，其次原路由名，最后 #（避免缺路由炸页面）。
     */
    function soho_route(string $name, array $params = []): string
    {
        foreach (['soho.' . $name, $name] as $candidate) {
            if (Route::has($candidate)) {
                return route($candidate, $params);
            }
        }
        return '#';
    }
}