<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ProductAttribute;
use App\Soho\Models\ProductAttributeValue;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    /**
     * 商品属性列表
     */
    public function index(Request $request)
    {
        $attributes = ProductAttribute::query()
            ->with('values')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('product-attributes.index', compact('attributes'));
    }

    /**
     * 创建商品属性（显示表单）
     */
    public function create()
    {
        return view('product-attributes.create');
    }

    /**
     * 保存新商品属性
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:soho_product_attributes,code',
            'type' => 'required|in:text,number,select,multi_select,date',
            'options' => 'nullable|array',
            'options.*.label' => 'required|string|max:100',
            'options.*.value' => 'required|string|max:100',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:1,0',
            'values' => 'nullable|array',
            'values.*.value_name' => 'required|string|max:255',
            'values.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $validated['status'] = (int) $validated['status'];

        // 处理选项数据 JSON 存储
        $options = $validated['options'] ?? [];
        $validated['options_json'] = !empty($options) ? json_encode($options) : null;
        unset($validated['options'], $validated['values']);

        $attribute = ProductAttribute::create($validated);

        // 保存属性值
        if (!empty($request->values)) {
            foreach ($request->values as $idx => $val) {
                ProductAttributeValue::create([
                    'tenant_id' => auth()->user()->tenant_id ?? 1,
                    'product_attribute_id' => $attribute->id,
                    'value_name' => $val['value_name'],
                    'sort_order' => $val['sort_order'] ?? $idx,
                ]);
            }
        }

        return redirect()->route('product-attribute.index')->with('success', '商品属性创建成功');
    }

    /**
     * 编辑商品属性（显示表单）
     */
    public function edit(ProductAttribute $productAttribute)
    {
        $productAttribute->load('values');
        return view('product-attributes.edit', compact('productAttribute'));
    }

    /**
     * 更新商品属性
     */
    public function update(Request $request, ProductAttribute $productAttribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:soho_product_attributes,code,' . $productAttribute->id,
            'type' => 'required|in:text,number,select,multi_select,date',
            'options' => 'nullable|array',
            'options.*.label' => 'required|string|max:100',
            'options.*.value' => 'required|string|max:100',
            'sort' => 'nullable|integer|min:0',
            'status' => 'required|in:1,0',
            'values' => 'nullable|array',
            'values.*.value_name' => 'required|string|max:255',
            'values.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['status'] = (int) $validated['status'];

        // 处理选项数据 JSON 存储
        $options = $validated['options'] ?? [];
        $validated['options_json'] = !empty($options) ? json_encode($options) : null;
        unset($validated['options']);

        $productAttribute->update($validated);

        // 同步属性值（先删除旧的，再创建新的）
        $productAttribute->values()->delete();
        if (!empty($request->values)) {
            foreach ($request->values as $idx => $val) {
                ProductAttributeValue::create([
                    'tenant_id' => auth()->user()->tenant_id ?? 1,
                    'product_attribute_id' => $productAttribute->id,
                    'value_name' => $val['value_name'],
                    'sort_order' => $val['sort_order'] ?? $idx,
                ]);
            }
        }

        return redirect()->route('product-attribute.index')->with('success', '商品属性更新成功');
    }

    /**
     * 删除商品属性
     */
    public function destroy(ProductAttribute $productAttribute)
    {
        // 先删除关联的属性值
        $productAttribute->values()->delete();
        $productAttribute->delete();

        return redirect()->route('product-attribute.index')->with('success', '商品属性删除成功');
    }
}
