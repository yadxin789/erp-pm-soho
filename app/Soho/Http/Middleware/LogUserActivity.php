<?php

namespace App\Soho\Http\Middleware;

use App\Soho\Models\OperationLog;
use Closure;
use Illuminate\Http\Request;

/**
 * 用户操作日志中间件
 * 自动记录用户在系统中的关键操作行为，绑定用户ID用于审计追踪
 */
class LogUserActivity
{
    /**
     * 关键操作的路由模式（用于识别重要操作）
     */
    protected array $actionRoutes = [
        // 数据创建/更新/删除操作
        'POST' => ['store', 'create', 'execute', 'handle', 'approve', 'reject', 'ship', 'cancel', 'reset', 'toggle', 'batch'],
        'PUT' => ['update', 'edit'],
        'PATCH' => ['update', 'toggle'],
        'DELETE' => ['destroy'],
    ];

    /**
     * 处理请求，记录操作日志
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $this->logActivity($request);
        } catch (\Exception $e) {
            // 日志记录失败不影响主业务
        }

        return $response;
    }

    /**
     * 记录用户操作
     */
    protected function logActivity(Request $request): void
    {
        $user = $request->user();

        // 未登录用户不记录
        if (!$user) {
            return;
        }

        $method = $request->method();
        $routeName = $request->route()?->getName() ?? '';

        // 只记录关键操作
        if (!$this->shouldLog($method, $routeName)) {
            return;
        }

        // 生成操作描述
        $action = $this->determineAction($method, $routeName);
        $description = $this->buildDescription($request, $action, $routeName);

        // 构建日志数据
        $logData = [
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => $this->extractModelType($routeName),
            'model_id' => $request->route()?->parameters() ? implode(',', $request->route()->parameters()) : null,
            'description' => $description,
            'changes' => $this->extractChanges($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        OperationLog::create($logData);
    }

    /**
     * 判断是否需要记录该操作
     */
    protected function shouldLog(string $method, string $routeName): bool
    {
        // GET 请求只记录关键页面访问（如登录、导出等）
        if ($method === 'GET') {
            return $this->isImportantGetRoute($routeName);
        }

        // 检查路由名称是否包含关键操作关键词
        $routeLower = strtolower($routeName);
        $actions = $this->actionRoutes[$method] ?? [];

        foreach ($actions as $action) {
            if (str_contains($routeLower, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断是否为重要的GET路由
     */
    protected function isImportantGetRoute(string $routeName): bool
    {
        $importantPrefixes = [
            'export',
            'download',
            'authorize',
            'check-auth',
        ];

        foreach ($importantPrefixes as $prefix) {
            if (str_contains(strtolower($routeName), $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 确定操作类型
     */
    protected function determineAction(string $method, string $routeName): string
    {
        $routeLower = strtolower($routeName);

        // 根据路由名称匹配操作类型
        if (str_contains($routeLower, 'approve')) {
            return 'approved';
        }
        if (str_contains($routeLower, 'reject')) {
            return 'rejected';
        }
        if (str_contains($routeLower, 'export') || str_contains($routeLower, 'download')) {
            return 'exported';
        }
        if (str_contains($routeLower, 'import') || str_contains($routeLower, 'upload')) {
            return 'imported';
        }
        if (str_contains($routeLower, 'publish')) {
            return 'published';
        }
        if (str_contains($routeLower, 'ship')) {
            return 'shipped';
        }
        if (str_contains($routeLower, 'cancel')) {
            return 'cancelled';
        }
        if (str_contains($routeLower, 'sync')) {
            return 'synced';
        }
        if (str_contains($routeLower, 'reset')) {
            return 'reset';
        }
        if (str_contains($routeLower, 'toggle') || str_contains($routeLower, 'status')) {
            return 'toggled';
        }
        if (str_contains($routeLower, 'execute') || str_contains($routeLower, 'batch')) {
            return 'executed';
        }
        if (str_contains($routeLower, 'handle')) {
            return 'handled';
        }
        if (str_contains($routeLower, 'assign')) {
            return 'assigned';
        }
        if (str_contains($routeLower, 'resolve')) {
            return 'resolved';
        }
        if (str_contains($routeLower, 'ignore')) {
            return 'ignored';
        }

        // 根据请求方法确定基本操作
        return match ($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            'GET' => 'viewed',
            default => 'action',
        };
    }

    /**
     * 构建操作描述
     */
    protected function buildDescription(Request $request, string $action, string $routeName): string
    {
        $user = $request->user();
        $userName = $user?->name ?? '未知用户';
        $userRole = $user?->roles->first()?->name ?? '未知角色';

        $routeName = $routeName ?: 'unknown';

        return sprintf(
            '用户[%s](角色:%s) 在 %s 执行了 %s 操作',
            $userName,
            $userRole,
            $routeName,
            $this->getActionLabel($action)
        );
    }

    /**
     * 获取操作类型的中文标签
     */
    protected function getActionLabel(string $action): string
    {
        $labels = [
            'created' => '创建',
            'updated' => '更新',
            'deleted' => '删除',
            'approved' => '审批通过',
            'rejected' => '审批拒绝',
            'exported' => '导出',
            'imported' => '导入',
            'published' => '发布',
            'shipped' => '发货',
            'cancelled' => '取消',
            'synced' => '同步',
            'reset' => '重置',
            'toggled' => '切换状态',
            'executed' => '执行',
            'handled' => '处理',
            'assigned' => '分配',
            'resolved' => '解决',
            'ignored' => '忽略',
            'viewed' => '查看',
            'refunded' => '退款',
        ];

        return $labels[$action] ?? $action;
    }

    /**
     * 从路由名称提取模型类型
     */
    protected function extractModelType(string $routeName): ?string
    {
        // 路由名格式: group.action (如: product.index, order.store)
        $parts = explode('.', $routeName);

        if (count($parts) >= 2) {
            return $parts[0]; // 返回模块名称，如 product, order, user
        }

        return null;
    }

    /**
     * 提取请求参数变化（用于记录修改内容）
     */
    protected function extractChanges(Request $request): ?array
    {
        $method = $request->method();

        // 只有写操作才记录变化
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return null;
        }

        $data = $request->except(['_token', '_method', 'password', 'password_confirmation']);

        if (empty($data)) {
            return null;
        }

        // 限制记录的字段数量，避免日志过大
        if (count($data) > 20) {
            $data = array_slice($data, 0, 20);
            $data['...truncated'] = true;
        }

        return $data;
    }
}