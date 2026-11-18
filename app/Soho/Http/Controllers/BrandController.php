<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('brand.index', compact('brands'));
    }

    public function create()
    {
        return view('brand.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        Brand::create($validated);

        return redirect()->route('brand.index')->with('success', '品牌创建成功');
    }

    public function edit(Brand $brand)
    {
        return view('brand.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $brand->update($validated);

        return redirect()->route('brand.index')->with('success', '品牌更新成功');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', '该品牌下有商品，无法删除');
        }

        $brand->delete();
        return redirect()->route('brand.index')->with('success', '品牌删除成功');
    }
}