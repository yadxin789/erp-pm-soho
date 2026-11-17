<?php

namespace App\Core\Support;

/**
 * 模块上下文（DDWL 三合一 · 彻底合并的执行基础，见 P0 D20）
 *
 * 作用：在"单一 Model + 统一表"的前提下，让同一个 Model 在 /erp、/pm、/soho
 * 下自动只读写本模块数据。上下文来源优先级：
 *   1) 路由组中间件显式指定（module:erp）
 *   2) 请求头 X-Module（API / 自动化）
 *   3) session 中的 active_module（子请求、跳转）
 *   4) URL 首段（/erp/...）
 *   5) 配置默认值（core）
 *
 * CLI / 队列任务没有 HTTP 上下文，必须显式 set 或用 run() 包裹。
 */
class ModuleContext
{
    protected static ?string $current = null;

    /** 当前模块标识，未设置时返回 null（表示"跨模块/不限"） */
    public static function current(): ?string
    {
        return self::$current;
    }

    /** 当前模块，未设置时回退到配置默认值 */
    public static function id(): string
    {
        return self::$current ?? config('modules.default', 'core');
    }

    public static function set(?string $module): void
    {
        self::$current = $module === '' ? null : $module;
    }

    public static function clear(): void
    {
        self::$current = null;
    }

    public static function is(string $module): bool
    {
        return self::$current === $module;
    }

    /** 是否处于"明确模块上下文"（core 视为无隔离需求） */
    public static function hasScope(): bool
    {
        return self::$current !== null && self::$current !== 'core';
    }

    /**
     * 在指定模块上下文中执行回调，执行完恢复原上下文（异常安全）。
     */
    public static function run(string $module, callable $callback)
    {
        $previous = self::$current;
        self::set($module);

        try {
            return $callback();
        } finally {
            self::$current = $previous;
        }
    }

    /** 全部可用模块标识 */
    public static function all(): array
    {
        return array_keys(config('modules.modules', []));
    }

    /** 该模块标识是否已在 config/modules.php 注册（且 enabled=true） */
    public static function exists(string $module): bool
    {
        $cfg = config("modules.modules.{$module}");

        return is_array($cfg) && ($cfg['enabled'] ?? true) === true;
    }
}
