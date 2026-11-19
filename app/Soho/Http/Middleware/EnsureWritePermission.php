<?php

namespace App\Soho\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * SOHO 写操作/表单页权限收口。
 *
 * 背景：大量路由组只用 `permission:{domain}.view` 守住整组，导致「只读角色能打开新建表单、
 * 甚至能直接 POST 提交」。此中间件按「控制器动作方法名 + 域」反推操作级权限点
 * （`product.create` / `brand.edit` / `coupon.delete` / `refund.approve` …），
 * 若该操作级权限点在库中确实存在，则要求持有；不存在则放行（不臆造权限点）。
 *
 * 口径与全站一致：无权限不返回 403，统一 302 回工作台并提示。
 */
class EnsureWritePermission
{
    /** 动作方法名 → 操作后缀 */
    private const OP_MAP = [
        'store' => 'create',
        'create' => 'create',
        'update' => 'edit',
        'edit' => 'edit',
        'destroy' => 'delete',
        'delete' => 'delete',
        'approve' => 'approve',
        'reject' => 'approve',
        'execute' => 'execute',
        'push' => 'push',
        'import' => 'import',
        'export' => 'export',
        'reset' => 'reset',
    ];

    /** 已知的操作级后缀（用于「域.后缀」探测） */
    private const OP_SUFFIXES = ['create', 'edit', 'delete', 'approve', 'execute', 'push', 'import', 'export', 'reset'];

    /** @var array<string,bool> 权限点存在性缓存 */
    private static array $exists = [];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest('/login');
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $op = $this->resolveOperation($request);
        if ($op === null) {
            return $next($request);
        }

        [$domain, $opName] = $op;
        $required = $this->existingPermissions($domain, $opName);

        if ($required === []) {
            return $next($request);
        }

        foreach ($required as $permission) {
            if (can_perm($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => '您当前角色无权执行该操作。'], 302);
        }

        return redirect()->route('soho.dashboard')->with('error', '您当前角色无权执行该操作。');
    }

    /**
     * 解析 [域, 操作后缀]；无法判定返回 null。
     *
     * @return array{0:string,1:string}|null
     */
    private function resolveOperation(Request $request): ?array
    {
        $action = $request->route()?->getActionName() ?? '';
        $method = '';
        if (preg_match('/@(\w+)$/', $action, $m)) {
            $method = $m[1];
        }

        $unsafe = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        // GET 只处理「新建/编辑表单页」；其余 GET 沿用路由自身的 view 级守卫
        if (! $unsafe && ! in_array($method, ['create', 'edit'], true)) {
            return null;
        }

        $opName = self::OP_MAP[$method] ?? null;
        if ($opName === null) {
            return null;
        }

        // 域：路由名 soho.{domain}.* 优先；否则取 uri 第一段
        $name = (string) $request->route()?->getName();
        $domain = '';
        if (str_starts_with($name, 'soho.')) {
            $parts = explode('.', $name);
            $domain = $parts[1] ?? '';
        }
        if ($domain === '') {
            $segments = explode('/', trim($request->path(), '/'));
            $domain = $segments[1] ?? ($segments[0] ?? '');
        }

        // 归一化常见别名，保证与权限点命名对齐
        $domain = str_replace(['-', '_'], ['_', '_'], $domain);
        $aliases = [
            'goods' => 'product',
            'platform_sku' => 'sku',
            'shop' => 'shop',
            'price' => 'price',
            'currencies' => 'currency',
            'tax_rate' => 'tax_rate',
            'approval_flows' => 'approval_flow',
            'express' => 'express',
        ];
        $domain = $aliases[$domain] ?? $domain;

        return [$domain, $opName];
    }

    /**
     * 该域该操作在库中实际存在哪些权限点（裸名 + soho. 前缀名）。
     *
     * @return list<string>
     */
    private function existingPermissions(string $domain, string $opName): array
    {
        $candidates = [];
        foreach (["{$domain}.{$opName}", "soho.{$domain}.{$opName}"] as $c) {
            if (! array_key_exists($c, self::$exists)) {
                self::$exists[$c] = DB::table('permissions')
                    ->where('guard_name', 'web')
                    ->where('name', $c)
                    ->exists();
            }
            if (self::$exists[$c]) {
                $candidates[] = $c;
            }
        }

        return $candidates;
    }
}
