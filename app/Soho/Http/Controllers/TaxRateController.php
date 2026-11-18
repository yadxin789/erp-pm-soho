<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * 显示税率列表
     * 支持按名称、国家/地区代码和省份筛选
     */
    public function index(Request $request)
    {
        $taxRates = TaxRate::when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('country_code', 'like', "%{$request->search}%");
            })
            ->when($request->country_code, function ($query) use ($request) {
                $query->where('country_code', $request->country_code);
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('tax-rates.index', compact('taxRates'));
    }

    /**
     * 创建税率表单页面
     */
    public function create()
    {
        return view('tax-rates.create');
    }

    /**
     * 保存新创建的税率
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'country_code' => 'required|string|max:10',
            'province' => 'nullable|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'is_compound' => 'boolean',
            'included_in_price' => 'boolean',
            'priority' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['is_compound'] = $validated['is_compound'] ?? false;
        $validated['included_in_price'] = $validated['included_in_price'] ?? false;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['status'] = $validated['status'] ?? true;

        TaxRate::create($validated);

        return redirect()->route('soho.tax-rate.index')->with('success', '税率创建成功');
    }

    /**
     * 编辑税率表单页面
     */
    public function edit(TaxRate $taxRate)
    {
        return view('tax-rates.edit', compact('taxRate'));
    }

    /**
     * 更新指定的税率
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'country_code' => 'required|string|max:10',
            'province' => 'nullable|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'is_compound' => 'boolean',
            'included_in_price' => 'boolean',
            'priority' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $taxRate->update($validated);

        return redirect()->route('soho.tax-rate.index')->with('success', '税率更新成功');
    }

    /**
     * 删除指定的税率
     */
    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();

        return redirect()->route('soho.tax-rate.index')->with('success', '税率删除成功');
    }
}