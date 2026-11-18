<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PriceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 价格规则控制器
 * 管理价格规则的增删改查，支持按类型、启用状态筛选，实现租户隔离
 */
class PriceRuleController extends Controller
{
    /**
     * 规则列表
     * 支持按规则类型、启用状态筛选，展示当前租户下的所有价格规则
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $rules = PriceRule::with(['creator:id,name'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($type = $request->input('type'), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($request->input('is_active') !== null, function ($query) use ($request) {
                $query->where('is_active', $request->input('is_active'));
            })
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(20);

        $typeLabels = PriceRule::getTypeLabels();
        $typeColors = PriceRule::getTypeColors();

        return view('price.price-rules.index', compact(
            'rules',
            'typeLabels',
            'typeColors',
            'search'
        ));
    }

    /**
     * 创建规则表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $typeLabels = PriceRule::getTypeLabels();
        $typeColors = PriceRule::getTypeColors();

        return view('price.price-rules.create', compact(
            'typeLabels',
            'typeColors'
        ));
    }

    /**
     * 保存新规则
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:markup,discount,fixed,range,tiered,competitor,cost_plus',
            'description' => 'nullable|string|max:500',
            'parameters' => 'nullable|array',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $userId = Auth::user()->id;

        $validated['tenant_id'] = $tenantId;
        $validated['created_by'] = $userId;

        if (isset($validated['min_price']) && isset($validated['max_price'])
            && $validated['min_price'] > $validated['max_price']) {
            return back()->withErrors(['min_price' => '最低价格不能高于最高价格'])->withInput();
        }

        PriceRule::create($validated);

        return redirect()->route('soho.price.rules.index')->with('success', '价格规则创建成功');
    }

    /**
     * 编辑规则表单
     *
     * @param PriceRule $priceRule
     * @return \Illuminate\View\View
     */
    public function edit(PriceRule $priceRule)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceRule->tenant_id !== $tenantId) {
            abort(403, '无权访问此规则');
        }

        $typeLabels = PriceRule::getTypeLabels();
        $typeColors = PriceRule::getTypeColors();

        return view('price.price-rules.edit', compact(
            'priceRule',
            'typeLabels',
            'typeColors'
        ));
    }

    /**
     * 更新规则
     *
     * @param Request $request
     * @param PriceRule $priceRule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PriceRule $priceRule)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceRule->tenant_id !== $tenantId) {
            abort(403, '无权修改此规则');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:markup,discount,fixed,range,tiered,competitor,cost_plus',
            'description' => 'nullable|string|max:500',
            'parameters' => 'nullable|array',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
        ]);

        if (isset($validated['min_price']) && isset($validated['max_price'])
            && $validated['min_price'] > $validated['max_price']) {
            return back()->withErrors(['min_price' => '最低价格不能高于最高价格'])->withInput();
        }

        $priceRule->update($validated);

        return redirect()->route('soho.price.rules.index')->with('success', '价格规则更新成功');
    }

    /**
     * 删除规则
     *
     * @param PriceRule $priceRule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PriceRule $priceRule)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceRule->tenant_id !== $tenantId) {
            abort(403, '无权删除此规则');
        }

        $priceRule->delete();

        return redirect()->route('soho.price.rules.index')->with('success', '价格规则删除成功');
    }
}