<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\AuthorizationFailure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 授权失败记录控制器
 * 用于查看和管理授权失败记录
 */
class AuthorizationFailureController extends Controller
{
    /**
     * 显示授权失败记录列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询授权失败记录
        $failures = AuthorizationFailure::with(['shop:id,shop_name,platform', 'platformAccount:id,account_name,platform_name', 'resolver:id,name,email'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('error_message', 'like', "%{$search}%")
                      ->orWhere('error_code', 'like', "%{$search}%")
                      ->orWhere('platform', 'like', "%{$search}%");
                });
            })
            ->when($failureType = $request->input('failure_type'), function ($query) use ($failureType) {
                $query->where('failure_type', $failureType);
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($platform = $request->input('platform'), function ($query) use ($platform) {
                $query->where('platform', $platform);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('failed_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('failed_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('failed_at')
            ->get();

        // 统计数据
        $statistics = $this->getStatistics($tenantId);

        // 平台列表（用于筛选）
        $platforms = AuthorizationFailure::when($tenantId, function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->distinct()->pluck('platform')->sort()->values();

        // 标签映射
        $failureTypeLabels = AuthorizationFailure::getFailureTypeLabels();
        $failureTypeColors = AuthorizationFailure::getFailureTypeColors();
        $statusLabels = AuthorizationFailure::getStatusLabels();
        $statusColors = AuthorizationFailure::getStatusColors();
        $targetTypeLabels = AuthorizationFailure::getTargetTypeLabels();

        return view('shop.authorization-failures.index', compact(
            'failures',
            'statistics',
            'platforms',
            'failureTypeLabels',
            'failureTypeColors',
            'statusLabels',
            'statusColors',
            'targetTypeLabels'
        ));
    }

    /**
     * 显示单条授权失败记录详情
     *
     * @param AuthorizationFailure $failure
     * @return \Illuminate\View\View
     */
    public function show(AuthorizationFailure $failure)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $failure->tenant_id !== $tenantId) {
            abort(403, '无权访问此记录');
        }

        $failure->load(['shop', 'platformAccount', 'resolver']);

        // 标签映射
        $failureTypeLabels = AuthorizationFailure::getFailureTypeLabels();
        $failureTypeColors = AuthorizationFailure::getFailureTypeColors();
        $statusLabels = AuthorizationFailure::getStatusLabels();
        $statusColors = AuthorizationFailure::getStatusColors();
        $targetTypeLabels = AuthorizationFailure::getTargetTypeLabels();

        return view('shop.authorization-failures.show', compact(
            'failure',
            'failureTypeLabels',
            'failureTypeColors',
            'statusLabels',
            'statusColors',
            'targetTypeLabels'
        ));
    }

    /**
     * 标记授权失败记录为已解决
     *
     * @param Request $request
     * @param AuthorizationFailure $failure
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolve(Request $request, AuthorizationFailure $failure)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $failure->tenant_id !== $tenantId) {
            abort(403, '无权操作此记录');
        }

        // 验证请求
        $request->validate([
            'resolution_note' => 'nullable|string|max:500',
        ]);

        // 更新状态
        $failure->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
            'resolution_note' => $request->input('resolution_note'),
        ]);

        return redirect()->route('shop.authorization-failures.show', $failure)
            ->with('success', '授权失败记录已标记为已解决');
    }

    /**
     * 获取统计数据
     *
     * @param int|null $tenantId
     * @return array
     */
    private function getStatistics(?int $tenantId): array
    {
        $query = AuthorizationFailure::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7);

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'resolving' => (clone $query)->where('status', 'resolving')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'ignored' => (clone $query)->where('status', 'ignored')->count(),
            'today' => (clone $query)->where('failed_at', '>=', $today)->count(),
            'week' => (clone $query)->where('failed_at', '>=', $weekAgo)->count(),
            'by_type' => (clone $query)
                ->selectRaw('failure_type, COUNT(*) as count')
                ->groupBy('failure_type')
                ->pluck('count', 'failure_type')
                ->toArray(),
        ];
    }
}