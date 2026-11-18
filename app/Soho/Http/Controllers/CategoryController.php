<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::with('parent')
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('category.index', compact('categories'));
    }

    public function create()
    {
        $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
        return view('category.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories',
            'parent_id' => 'nullable|exists:soho_product_categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        ProductCategory::create($validated);

        return redirect()->route('category.index')->with('success', '分类创建成功');
    }

    public function edit(ProductCategory $category)
    {
        $categories = ProductCategory::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();
        return view('category.edit', compact('category', 'categories'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('soho_product_categories')->ignore($category->id)],
            'parent_id' => 'nullable|exists:soho_product_categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('category.index')->with('success', '分类更新成功');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->children()->count() > 0) {
            return back()->with('error', '该分类有子分类，无法删除');
        }

        if ($category->products()->count() > 0) {
            return back()->with('error', '该分类下有商品，无法删除');
        }

        $category->delete();
        return redirect()->route('category.index')->with('success', '分类删除成功');
    }
}