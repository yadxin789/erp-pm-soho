<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * 促销活动列表
     */
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::where('status', 'active')->count(),
            'draft' => Promotion::where('status', 'draft')->count(),
            'expired' => Promotion::where('status', 'expired')->count(),
        ];

        return view('promotion.index', compact('promotions', 'stats'));
    }

    /**
     * 创建促销活动（显示表单）
     */
    public function create()
    {
        return view('promotion.create');
    }

    /**
     * 保存新促销活动
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::in(['fixed_discount', 'percentage', 'buy_get', 'flash_sale', 'bundle'])],
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => ['required', Rule::in(['draft', 'active', 'inactive', 'expired'])],
            'stackable' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $validated['stackable'] = $validated['stackable'] ?? false;

        Promotion::create($validated);

        return redirect()->route('soho.promotion.index')->with('success', '促销活动创建成功');
    }

    /**
     * 编辑促销活动（显示表单）
     */
    public function edit(Promotion $promotion)
    {
        return view('promotion.edit', compact('promotion'));
    }

    /**
     * 更新促销活动
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::in(['fixed_discount', 'percentage', 'buy_get', 'flash_sale', 'bundle'])],
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => ['required', Rule::in(['draft', 'active', 'inactive', 'expired'])],
            'stackable' => 'boolean',
        ]);

        $promotion->update($validated);

        return redirect()->route('soho.promotion.index')->with('success', '促销活动更新成功');
    }

    /**
     * 删除促销活动
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('soho.promotion.index')->with('success', '促销活动删除成功');
    }

    /**
     * 切换促销活动状态
     */
    public function toggleStatus(Promotion $promotion)
    {
        $promotion->status = $promotion->status === 'active' ? 'inactive' : 'active';
        $promotion->save();

        return back()->with('success', '促销活动状态已切换');
    }
}
