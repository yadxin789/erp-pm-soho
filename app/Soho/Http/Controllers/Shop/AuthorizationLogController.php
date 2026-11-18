<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\AuthorizationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 授权操作日志控制器
 * 用于查看和管理授权操作历史记录
 */
class AuthorizationLogController extends Controller
{
    /**
     * 显示授权操作日志列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询授权操作日志
        $logs = AuthorizationLog::with(['shop:id,shop_name,platform', 'platformAccount:id,account_name,platform_name', 'operator:id,name,email'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('operator_name', 'like', "%{$search}%")
                      ->orWhere('detail', 'like', "%{$search}%")
                      ->orWhere('error_message', 'like', "%{$search}%");
                });
            })
            ->when($operationType = $request->input('operation_type'), function ($query) use ($operationType) {
                $query->where('operation_type', $operationType);
            })
            ->when($result = $request->input('result'), function ($query) use ($result) {
                $query->where('result', $result);
            })
            ->when($targetType = $request->input('target_type'), function ($query) use ($targetType) {
                $query->where('target_type', $targetType);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('operated_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('operated_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('operated_at')
            ->get();

        // 统计数据
        $statistics = $this->getStatistics($tenantId);

        // 标签映射
        $operationTypeLabels = AuthorizationLog::getOperationTypeLabels();
        $operationTypeColors = AuthorizationLog::getOperationTypeColors();
        $resultLabels = AuthorizationLog::getResultLabels();
        $resultColors = AuthorizationLog::getResultColors();
        $targetTypeLabels = AuthorizationLog::getTargetTypeLabels();

        return view('shop.authorization-logs.index', compact(
            'logs',
            'statistics',
            'operationTypeLabels',
            'operationTypeColors',
            'resultLabels',
            'resultColors',
            'targetTypeLabels'
        ));
    }

    /**
     * 显示单条授权操作日志详情
     *
     * @param AuthorizationLog $log
     * @return \Illuminate\View\View
     */
    public function show(AuthorizationLog $log)
    {
        // 验证租户权限
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $log->tenant_id !== $tenantId) {
            abort(403, '无权访问此记录');
        }

        $log->load(['shop', 'platformAccount', 'operator']);

        return view('shop.authorization-logs.show', compact('log'));
    }

    /**
     * 获取统计数据
     *
     * @param int|null $tenantId
     * @return array
     */
    private function getStatistics(?int $tenantId): array
    {
        $query = AuthorizationLog::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7);
        $monthAgo = now()->subDays(30);

        return [
            'total' => $query->count(),
            'success' => (clone $query)->where('result', 'success')->count(),
            'failed' => (clone $query)->where('result', 'failed')->count(),
            'today' => (clone $query)->where('operated_at', '>=', $today)->count(),
            'week' => (clone $query)->where('operated_at', '>=', $weekAgo)->count(),
            'month' => (clone $query)->where('operated_at', '>=', $monthAgo)->count(),
            'by_type' => (clone $query)
                ->selectRaw('operation_type, COUNT(*) as count')
                ->groupBy('operation_type')
                ->pluck('count', 'operation_type')
                ->toArray(),
        ];
    }
}