<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * 显示货币列表
     * 支持按货币代码和名称搜索
     */
    public function index(Request $request)
    {
        $currencies = Currency::when($request->search, function ($query) use ($request) {
                $query->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('code', 'asc')
            ->paginate(20);

        return view('currencies.index', compact('currencies'));
    }

    /**
     * 创建货币表单页面
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * 保存新创建的货币
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:soho_currencies,code',
            'name' => 'required|string|max:50',
            'symbol' => 'nullable|string|max:10',
            'rate' => 'required|numeric|min:0',
            'decimal_places' => 'nullable|integer|min:0|max:4',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ]);

        $validated['decimal_places'] = $validated['decimal_places'] ?? 2;
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['status'] = $validated['status'] ?? true;

        if ($validated['is_default']) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        Currency::create($validated);

        return redirect()->route('soho.currencies.index')->with('success', '货币创建成功');
    }

    /**
     * 编辑货币表单页面
     */
    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    /**
     * 更新指定的货币
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('soho_currencies', 'code')->ignore($currency->id)],
            'name' => 'required|string|max:50',
            'symbol' => 'nullable|string|max:10',
            'rate' => 'required|numeric|min:0',
            'decimal_places' => 'nullable|integer|min:0|max:4',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ]);

        if (!empty($validated['is_default']) && !$currency->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($validated);

        return redirect()->route('soho.currencies.index')->with('success', '货币更新成功');
    }

    /**
     * 删除指定的货币
     */
    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', '默认货币无法删除，请先设置其他货币为默认');
        }

        $currency->delete();

        return redirect()->route('soho.currencies.index')->with('success', '货币删除成功');
    }

    /**
     * 设置指定货币为默认货币
     */
    public function setDefault(Currency $currency)
    {
        Currency::where('is_default', true)->update(['is_default' => false]);

        $currency->is_default = true;
        $currency->save();

        return redirect()->route('soho.currencies.index')->with('success', "已将 {$currency->code} 设为默认货币");
    }
}