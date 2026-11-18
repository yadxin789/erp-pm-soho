<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PriceChangeRequest;
use App\Soho\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 调价申请控制器
 * 管理价格变更申请的创建、编辑、查看和取消，支持自动计算变动金额和百分比
 */
class PriceChangeRequestController extends Controller
{
    /**
     * 调价申请列表
     * 支持按状态、商品、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $requests = PriceChangeRequest::with(['product', 'requester:id,name'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', "%{$search}%")
                      ->orWhere('remark', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%");
                });
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
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

        $statusLabels = PriceChangeRequest::getStatusLabels();
        $statusColors = PriceChangeRequest::getStatusColors();

        return view('price.price-change-requests.index', compact(
            'requests',
            'statusLabels',
            'statusColors',
            'search'
        ));
    }

    /**
     * 创建调价申请
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->with(['skus'])
            ->orderBy('name')
            ->get();

        $statusLabels = PriceChangeRequest::getStatusLabels();

        return view('price.price-change-requests.create', compact(
            'products',
            'statusLabels'
        ));
    }

    /**
     * 保存调价申请
     * 自动计算变动金额和百分比
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:soho_products,id',
            'sku_id' => 'nullable|integer|exists:soho_product_skus,id',
            'sku_code' => 'nullable|string|max:100',
            'current_price' => 'required|numeric|min:0',
            'requested_price' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
            'remark' => 'nullable|string|max:1000',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();

        $changeAmount = $validated['requested_price'] - $validated['current_price'];
        $changePercentage = $validated['current_price'] > 0
            ? round(($changeAmount / $validated['current_price']) * 100, 2)
            : 0;

        $validated['tenant_id'] = $tenantId;
        $validated['change_amount'] = $changeAmount;
        $validated['change_percentage'] = $changePercentage;
        $validated['status'] = 'pending';
        $validated['requested_by'] = $user->id;
        $validated['requested_by_name'] = $user->name ?? $user->email;
        $validated['requested_at'] = now();

        PriceChangeRequest::create($validated);

        return redirect()->route('soho.price.change.requests.index')->with('success', '调价申请创建成功');
    }

    /**
     * 申请详情
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

        return view('price.price-change-requests.show', compact(
            'priceChangeRequest',
            'statusLabels',
            'statusColors'
        ));
    }

    /**
     * 编辑申请（仅待审批状态可编辑）
     *
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\View\View
     */
    public function edit(PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权编辑此申请');
        }

        if (!$priceChangeRequest->isPending()) {
            return redirect()->route('soho.price.change.requests.show', $priceChangeRequest)
                ->with('error', '仅待审批状态的申请可以编辑');
        }

        $products = Product::where('tenant_id', $tenantId)
            ->with(['skus'])
            ->orderBy('name')
            ->get();

        return view('price.price-change-requests.edit', compact(
            'priceChangeRequest',
            'products'
        ));
    }

    /**
     * 更新申请
     *
     * @param Request $request
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权修改此申请');
        }

        if (!$priceChangeRequest->isPending()) {
            return back()->with('error', '仅待审批状态的申请可以编辑');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:soho_products,id',
            'sku_id' => 'nullable|integer|exists:soho_product_skus,id',
            'sku_code' => 'nullable|string|max:100',
            'current_price' => 'required|numeric|min:0',
            'requested_price' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
            'remark' => 'nullable|string|max:1000',
        ]);

        $changeAmount = $validated['requested_price'] - $validated['current_price'];
        $changePercentage = $validated['current_price'] > 0
            ? round(($changeAmount / $validated['current_price']) * 100, 2)
            : 0;

        $validated['change_amount'] = $changeAmount;
        $validated['change_percentage'] = $changePercentage;

        $priceChangeRequest->update($validated);

        return redirect()->route('soho.price.change.requests.show', $priceChangeRequest)
            ->with('success', '调价申请更新成功');
    }

    /**
     * 取消申请
     *
     * @param PriceChangeRequest $priceChangeRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PriceChangeRequest $priceChangeRequest)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeRequest->tenant_id !== $tenantId) {
            abort(403, '无权操作此申请');
        }

        if (!$priceChangeRequest->isPending()) {
            return back()->with('error', '仅待审批状态的申请可以取消');
        }

        $priceChangeRequest->update(['status' => 'cancelled']);

        return redirect()->route('soho.price.change.requests.index')->with('success', '调价申请已取消');
    }
}