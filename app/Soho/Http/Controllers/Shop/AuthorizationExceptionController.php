<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\AuthorizationException;
use App\Soho\Models\Shop;
use App\Soho\Models\PlatformAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 授权异常控制器
 * 用于查看和管理授权异常，支持标记处理状态
 */
class AuthorizationExceptionController extends Controller
{
    /**
     * 显示授权异常列表
     * 支持按店铺、平台账号、异常类型、级别、状态、时间范围筛选
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 构建查询
        $query = AuthorizationException::with(['shop:id,shop_name,platform', 'platformAccount:id,account_name,platform_name', 'handler:id,name,email'])
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        // 关键词搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('detail', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhere('handle_note', 'like', "%{$search}%");
            });
        }

        // 店铺筛选
        if ($shopId = $request->input('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        // 平台账号筛选
        if ($platformAccountId = $request->input('platform_account_id')) {
            $query->where('platform_account_id', $platformAccountId);
        }

        // 异常类型筛选
        if ($exceptionType = $request->input('exception_type')) {
            $query->where('exception_type', $exceptionType);
        }

        // 异常级别筛选
        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        // 状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 时间范围筛选
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom || $dateTo) {
            $query->where(function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->where('exception_at', '>=', $dateFrom . ' 00:00:00');
                }
                if ($dateTo) {
                    $q->where('exception_at', '<=', $dateTo . ' 23:59:59');
                }
            });
        }

        // 排序并获取结果
        $exceptions = $query->orderByDesc('exception_at')->get();

        // 统计数据
        $statistics = $this->getStatistics($tenantId);

        // 获取标签映射
        $exceptionTypeLabels = AuthorizationException::getExceptionTypeLabels();
        $exceptionTypeColors = AuthorizationException::getExceptionTypeColors();
        $exceptionTypeIcons = AuthorizationException::getExceptionTypeIcons();
        $severityLabels = AuthorizationException::getSeverityLabels();
        $severityColors = AuthorizationException::getSeverityColors();
        $statusLabels = AuthorizationException::getStatusLabels();
        $statusColors = AuthorizationException::getStatusColors();
        $handleMethodLabels = AuthorizationException::getHandleMethodLabels();
        $targetTypeLabels = AuthorizationException::getTargetTypeLabels();

        // 获取店铺和平台账号列表（用于筛选）
        $shops = Shop::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->get(['id', 'shop_name', 'platform']);

        $platformAccounts = PlatformAccount::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->get(['id', 'account_name', 'platform_name']);

        return view('shop.authorization-exceptions.index', compact(
            'exceptions',
            'statistics',
            'exceptionTypeLabels',
            'exceptionTypeColors',
            'exceptionTypeIcons',
            'severityLabels',
            'severityColors',
            'statusLabels',
            'statusColors',
            'handleMethodLabels',
            'targetTypeLabels',
            'shops',
            'platformAccounts'
        ));
    }

    /**
     * 显示单条授权异常详情
     */
    public function show(AuthorizationException $authorizationException)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $authorizationException->tenant_id !== $tenantId) {
            abort(403, '无权访问此记录');
        }

        // 加载关联数据
        $authorizationException->load(['shop', 'platformAccount', 'handler']);

        // 获取标签
        $exceptionTypeLabels = AuthorizationException::getExceptionTypeLabels();
        $exceptionTypeColors = AuthorizationException::getExceptionTypeColors();
        $exceptionTypeIcons = AuthorizationException::getExceptionTypeIcons();
        $severityLabels = AuthorizationException::getSeverityLabels();
        $severityColors = AuthorizationException::getSeverityColors();
        $statusLabels = AuthorizationException::getStatusLabels();
        $statusColors = AuthorizationException::getStatusColors();
        $handleMethodLabels = AuthorizationException::getHandleMethodLabels();
        $targetTypeLabels = AuthorizationException::getTargetTypeLabels();

        return view('shop.authorization-exceptions.show', compact(
            'authorizationException',
            'exceptionTypeLabels',
            'exceptionTypeColors',
            'exceptionTypeIcons',
            'severityLabels',
            'severityColors',
            'statusLabels',
            'statusColors',
            'handleMethodLabels',
            'targetTypeLabels'
        ));
    }

    /**
     * 标记授权异常为已处理
     */
    public function handle(Request $request, AuthorizationException $authorizationException)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $authorizationException->tenant_id !== $tenantId) {
            abort(403, '无权访问此记录');
        }

        // 验证请求
        $request->validate([
            'handle_method' => 'required|in:manual,auto,system,ignored',
            'handle_note' => 'nullable|string|max:500',
        ]);

        // 更新状态
        $authorizationException->update([
            'status' => 'handled',
            'handled_at' => now(),
            'handled_by' => Auth::id(),
            'handler_name' => Auth::user()->name,
            'handle_method' => $request->input('handle_method'),
            'handle_note' => $request->input('handle_note'),
        ]);

        return redirect()->route('soho.shop.authorization-exceptions.show', $authorizationException)
            ->with('success', '授权异常已标记为已处理');
    }

    /**
     * 获取统计数据
     */
    private function getStatistics(?int $tenantId): array
    {
        $query = AuthorizationException::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'handling' => (clone $query)->where('status', 'handling')->count(),
            'handled' => (clone $query)->where('status', 'handled')->count(),
            'ignored' => (clone $query)->where('status', 'ignored')->count(),
            'critical' => (clone $query)->where('severity', 'critical')->count(),
            'warning' => (clone $query)->where('severity', 'warning')->count(),
            'info' => (clone $query)->where('severity', 'info')->count(),
            'by_type' => (clone $query)
                ->selectRaw('exception_type, COUNT(*) as count')
                ->groupBy('exception_type')
                ->pluck('count', 'exception_type')
                ->toArray(),
        ];
    }
}