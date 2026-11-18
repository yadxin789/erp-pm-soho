<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\TokenRefreshLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Token刷新记录控制器
 * 用于查看和管理Token刷新历史记录
 */
class TokenRefreshLogController extends Controller
{
    /**
     * 显示Token刷新记录列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询Token刷新记录
        $logs = TokenRefreshLog::with(['shop:id,shop_name,platform', 'platformAccount:id,account_name,platform_name', 'operator:id,name,email'])
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
            ->when($result = $request->input('result'), function ($query) use ($result) {
                $query->where('result', $result);
            })
            ->when($refreshMethod = $request->input('refresh_method'), function ($query) use ($refreshMethod) {
                $query->where('refresh_method', $refreshMethod);
            })
            ->when($platform = $request->input('platform'), function ($query) use ($platform) {
                $query->where('platform', $platform);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('refreshed_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('refreshed_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('refreshed_at')
            ->get();

        // 统计数据
        $statistics = $this->getStatistics($tenantId);

        // 平台列表（用于筛选）
        $platforms = TokenRefreshLog::when($tenantId, function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->distinct()->pluck('platform')->sort()->values();

        // 标签映射
        $resultLabels = TokenRefreshLog::getResultLabels();
        $resultColors = TokenRefreshLog::getResultColors();
        $refreshMethodLabels = TokenRefreshLog::getRefreshMethodLabels();
        $refreshMethodColors = TokenRefreshLog::getRefreshMethodColors();
        $targetTypeLabels = TokenRefreshLog::getTargetTypeLabels();

        return view('shop.token-refresh-logs.index', compact(
            'logs',
            'statistics',
            'platforms',
            'resultLabels',
            'resultColors',
            'refreshMethodLabels',
            'refreshMethodColors',
            'targetTypeLabels'
        ));
    }

    /**
     * 显示单条Token刷新记录详情
     *
     * @param TokenRefreshLog $log
     * @return \Illuminate\View\View
     */
    public function show(TokenRefreshLog $log)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $log->tenant_id !== $tenantId) {
            abort(403, '无权访问此记录');
        }

        $log->load(['shop', 'platformAccount', 'operator']);

        // 标签映射
        $resultLabels = TokenRefreshLog::getResultLabels();
        $resultColors = TokenRefreshLog::getResultColors();
        $refreshMethodLabels = TokenRefreshLog::getRefreshMethodLabels();
        $refreshMethodColors = TokenRefreshLog::getRefreshMethodColors();
        $targetTypeLabels = TokenRefreshLog::getTargetTypeLabels();

        return view('shop.token-refresh-logs.show', compact(
            'log',
            'resultLabels',
            'resultColors',
            'refreshMethodLabels',
            'refreshMethodColors',
            'targetTypeLabels'
        ));
    }

    /**
     * 获取统计数据
     *
     * @param int|null $tenantId
     * @return array
     */
    private function getStatistics(?int $tenantId): array
    {
        $query = TokenRefreshLog::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7);
        $monthAgo = now()->subDays(30);

        return [
            'total' => $query->count(),
            'success' => (clone $query)->where('result', 'success')->count(),
            'failed' => (clone $query)->where('result', 'failed')->count(),
            'today' => (clone $query)->where('refreshed_at', '>=', $today)->count(),
            'week' => (clone $query)->where('refreshed_at', '>=', $weekAgo)->count(),
            'month' => (clone $query)->where('refreshed_at', '>=', $monthAgo)->count(),
            'by_method' => (clone $query)
                ->selectRaw('refresh_method, COUNT(*) as count')
                ->groupBy('refresh_method')
                ->pluck('count', 'refresh_method')
                ->toArray(),
            'by_platform' => (clone $query)
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
        ];
    }
}