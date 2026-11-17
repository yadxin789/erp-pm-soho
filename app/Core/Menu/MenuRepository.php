<?php

namespace App\Core\Menu;

use App\Core\Models\User;

/**
 * 统一菜单仓储（TC-08）。
 *
 * 数据来源：resources/menu/{module}.php —— 由 database/scripts/menu_build.php
 * 从 docs/menu/baseline-menu.json 生成，基线来自三套源系统真实菜单定义：
 *   erp  → D:\laravel12-erp\resources\views\partials\sidebar_main.blade.php（46 项 / 11 组）
 *   pm   → E:\my-pm\pm-server-openEuler\resources\views\layouts\app.blade.php（31 项，去重前 69 条链接）
 *   soho → E:\ddwl-soho\app\Support\MenuConfig.php（232 项，扁平）
 *
 * 可见性判定与原系统一致：
 *   permission 有值 → 走 RBAC 权限点；
 *   roles 有值      → 走模块内角色白名单（ERP 原系统就是角色-模块守卫，没有权限点）；
 *   两者都为空      → 模块内公共入口，登录即可见。
 */
class MenuRepository
{
    /** @var array<string, array<int, array<string, mixed>>|null> */
    private static array $cache = [];

    public static function all(string $module): array
    {
        if (array_key_exists($module, self::$cache) && self::$cache[$module] !== null) {
            return self::$cache[$module];
        }

        $path = resource_path('menu/'.$module.'.php');
        if (! is_file($path)) {
            return self::$cache[$module] = [];
        }

        return self::$cache[$module] = require $path;
    }

    /**
     * 当前用户在该模块可见的菜单项（保持 sort 升序）。
     */
    public static function visible(string $module, ?User $user): array
    {
        $items = self::all($module);

        $filtered = array_values(array_filter($items, function (array $item) use ($user) {
            return self::canSee($item, $user);
        }));

        usort($filtered, fn (array $a, array $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $filtered;
    }

    public static function canSee(array $item, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $permission = $item['permission'] ?? null;
        $roles = $item['roles'] ?? null;

        if ($permission !== null && $permission !== '' && ! $user->can($permission)) {
            return false;
        }

        if (is_array($roles) && $roles !== []) {
            $mine = $user->roles->pluck('name')->all();
            if (array_intersect($roles, $mine) === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * 按 group 聚合，组的顺序 = 组内最小 sort。
     *
     * @return array<int, array{group:string, items:array<int, array<string, mixed>>}>
     */
    public static function grouped(array $items): array
    {
        $buckets = [];
        foreach ($items as $item) {
            $group = $item['group'] ?? '其他';
            $buckets[$group][] = $item;
        }

        $groups = [];
        foreach ($buckets as $group => $list) {
            $min = min(array_map(fn ($i) => $i['sort'] ?? 0, $list));
            $groups[] = ['group' => $group, 'items' => $list, 'sort' => $min];
        }
        usort($groups, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $groups;
    }

    /** 菜单项总数（基线条数） */
    public static function count(string $module): int
    {
        return count(self::all($module));
    }

    /** 路由名是否已在当前应用注册（模块迁入前会有一批未注册） */
    public static function routeExists(string $routeName): bool
    {
        return \Illuminate\Support\Facades\Route::has($routeName);
    }
}
