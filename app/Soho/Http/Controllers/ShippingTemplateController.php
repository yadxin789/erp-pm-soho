<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ShippingTemplate;
use Illuminate\Http\Request;

class ShippingTemplateController extends Controller
{
    /**
     * 运费模板列表
     * 支持按模板名称、省份、状态进行筛选
     */
    public function index(Request $request)
    {
        $templates = ShippingTemplate::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->province, function ($query) use ($request) {
                $query->where('province', $request->province);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('shipping-templates.index', compact('templates'));
    }

    /**
     * 创建运费模板（显示表单）
     */
    public function create()
    {
        return view('shipping-templates.create');
    }

    /**
     * 保存新运费模板
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'province' => 'nullable|string|max:50',
            'first_weight' => 'required|numeric|min:0.01',
            'first_fee' => 'required|numeric|min:0',
            'additional_weight' => 'required|numeric|min:0.01',
            'additional_fee' => 'required|numeric|min:0',
            'free_shipping_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;

        ShippingTemplate::create($validated);

        return redirect()->route('soho.shipping-templates.index')->with('success', '运费模板创建成功');
    }

    /**
     * 编辑运费模板（显示表单）
     */
    public function edit(ShippingTemplate $shippingTemplate)
    {
        return view('shipping-templates.edit', compact('shippingTemplate'));
    }

    /**
     * 更新运费模板
     */
    public function update(Request $request, ShippingTemplate $shippingTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'province' => 'nullable|string|max:50',
            'first_weight' => 'required|numeric|min:0.01',
            'first_fee' => 'required|numeric|min:0',
            'additional_weight' => 'required|numeric|min:0.01',
            'additional_fee' => 'required|numeric|min:0',
            'free_shipping_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $shippingTemplate->update($validated);

        return redirect()->route('soho.shipping-templates.index')->with('success', '运费模板更新成功');
    }

    /**
     * 删除运费模板
     */
    public function destroy(ShippingTemplate $shippingTemplate)
    {
        $shippingTemplate->delete();

        return redirect()->route('soho.shipping-templates.index')->with('success', '运费模板删除成功');
    }

    /**
     * 计算运费
     * 根据运费模板和传入的重量计算实际运费
     * 支持免费配送金额判断
     */
    public function calculateFee(Request $request, ShippingTemplate $shippingTemplate)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:0.01',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $weight = $validated['weight'];
        $orderAmount = $validated['order_amount'] ?? 0;

        // 判断是否满足免费配送条件
        if ($shippingTemplate->free_shipping_amount && $orderAmount >= $shippingTemplate->free_shipping_amount) {
            $fee = 0;
            $freeShipping = true;
        } else {
            $fee = $shippingTemplate->calculateFee($weight);
            $freeShipping = false;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'weight' => $weight,
                'order_amount' => $orderAmount,
                'fee' => $fee,
                'free_shipping' => $freeShipping,
                'template_name' => $shippingTemplate->name,
            ],
        ]);
    }
}