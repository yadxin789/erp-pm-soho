<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingMethodController extends Controller
{
    /**
     * 配送方式列表
     * 支持按名称、类型、状态进行筛选
     */
    public function index(Request $request)
    {
        $shippingMethods = ShippingMethod::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', (int) $request->status);
            })
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => ShippingMethod::count(),
            'active' => ShippingMethod::where('status', 1)->count(),
            'inactive' => ShippingMethod::where('status', 0)->count(),
        ];

        return view('shipping-method.index', compact('shippingMethods', 'stats'));
    }

    /**
     * 创建配送方式（显示表单）
     */
    public function create()
    {
        return view('shipping-method.create');
    }

    /**
     * 保存新配送方式
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:soho_shipping_methods,code',
            'type' => ['required', Rule::in(['fixed', 'weight', 'quantity', 'region'])],
            'status' => 'required|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $validated['sort'] = $validated['sort'] ?? 0;

        ShippingMethod::create($validated);

        return redirect()->route('shipping-method.index')->with('success', '配送方式创建成功');
    }

    /**
     * 编辑配送方式（显示表单）
     */
    public function edit(ShippingMethod $shippingMethod)
    {
        return view('shipping-method.edit', compact('shippingMethod'));
    }

    /**
     * 更新配送方式
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:50', Rule::unique('soho_shipping_methods', 'code')->ignore($shippingMethod->id)],
            'type' => ['required', Rule::in(['fixed', 'weight', 'quantity', 'region'])],
            'status' => 'required|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
        ]);

        $shippingMethod->update($validated);

        return redirect()->route('shipping-method.index')->with('success', '配送方式更新成功');
    }

    /**
     * 删除配送方式
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();

        return redirect()->route('shipping-method.index')->with('success', '配送方式删除成功');
    }
}
