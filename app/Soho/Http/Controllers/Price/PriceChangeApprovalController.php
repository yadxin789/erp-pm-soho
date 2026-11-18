<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PriceChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 调价审批控制器
 * 提供价格变更申请的审批功能，包括待审批列表、审批详情、批准和驳回操作
 * 仅允许具有审批角色的用户访问
 */
class PriceChangeApprovalController extends Controller
{
    /**
     * 待审批列表
     * 展示当前租户下所有待审批的调价申请
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $requests = PriceChangeRequest::with(['product', 'requester:id,name'])
            ->where('status', 'pending')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%");
                });
            })
            ->when($productId = $request->input('product_id'), function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('requested_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('requested_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('requested_at')
            ->paginate(20);

        $statistics = $this->getPendingStatistics($tenantId);
        $statusLabels = PriceChangeRequest::getStatusLabels();
        $statusColors = PriceChangeRequest::getStatusColors();

        return view('price.price-change-approvals.index', compact(
            'requests',
            'statistics',
            'statusLabels',
            'statusColors',
            'search'
        ));
    }

    /**
     * 审批详情
     *
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\View\View
     */
    public function show(PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权查看此申请');
        }

        $priceChangeRequest->load(['product', 'requester:id,name,email', 'approver:id,name,email']);

        $statusLabels = PriceChangeRequest::getStatusLabels();
        $statusColors = PriceChangeRequest::getStatusColors();

        return view('price.price-change-approvals.show', compact(
            'priceChangeRequest',
            'statusLabels',
            'statusColors'
        ));
    }

    /**
     * 批准申请
     *
     * @param Request $request
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权审批此申请');
        }

        if (!$priceChangeRequest->isPending()) {
            return back()->with('error', '该申请不是待审批状态，无法批准');
        }

        $validated = $request->validate([
            'approval_remark' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        $priceChangeRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_by_name' => $user->name ?? $user->email,
            'approval_remark' => $validated['approval_remark'] ?? null,
            'approved_at' => now(),
        ]);

        return redirect()->route('price-change-approvals.index')
            ->with('success', '调价申请已批准');
    }

    /**
     * 驳回申请
     *
     * @param Request $request
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权审批此申请');
        }

        if (!$priceChangeRequest->isPending()) {
            return back()->with('error', '该申请不是待审批状态，无法驳回');
        }

        $validated = $request->validate([
            'approval_remark' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $priceChangeRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_by_name' => $user->name ?? $user->email,
            'approval_remark' => $validated['approval_remark'],
            'approved_at' => now(),
        ]);

        return redirect()->route('price-change-approvals.index')
            ->with('success', '调价申请已驳回');
    }

    /**
     * 获取待审批统计数据
     *
     * @param int|null $tenantId
     * @return array
     */
    private function getPendingStatistics(?int $tenantId): array
    {
        $query = PriceChangeRequest::where('status', 'pending')
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        $pendingCount = (clone $query)->count();

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7);

        $totalChangeAmount = (clone $query)->sum('change_amount');
        $avgChangePercentage = $pendingCount > 0
            ? (clone $query)->avg('change_percentage')
            : 0;

        return [
            'pending_count' => $pendingCount,
            'today_new' => (clone $query)->where('requested_at', '>=', $today)->count(),
            'week_new' => (clone $query)->where('requested_at', '>=', $weekAgo)->count(),
            'total_change_amount' => $totalChangeAmount,
            'avg_change_percentage' => round($avgChangePercentage ?? 0, 2),
        ];
    }
}