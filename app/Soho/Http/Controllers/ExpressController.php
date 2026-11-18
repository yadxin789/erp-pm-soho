<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ExpressCompany;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpressController extends Controller
{
    public function index(Request $request)
    {
        $companies = ExpressCompany::when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('express.index', compact('companies'));
    }

    public function create()
    {
        return view('express.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:soho_express_company',
            'website' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:30',
              'taobao_code' => 'nullable|string|max:50',
              'jd_code' => 'nullable|string|max:50',
              'pdd_code' => 'nullable|string|max:50',
              'amazon_code' => 'nullable|string|max:50',
            'sort_order' => 'integer|min:0',
            'status' => 'integer|in:0,1',
        ]);

        $validated['status'] = $validated['status'] ?? 1;
        ExpressCompany::create($validated);

        return redirect()->route('soho.express.index')->with('success', '快递公司创建成功');
    }

    public function edit(ExpressCompany $express)
    {
        return view('express.edit', compact('express'));
    }

    public function update(Request $request, ExpressCompany $express)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:50', Rule::unique('express_company')->ignore($express->id)],
            'website' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:30',
              'taobao_code' => 'nullable|string|max:50',
              'jd_code' => 'nullable|string|max:50',
              'pdd_code' => 'nullable|string|max:50',
              'amazon_code' => 'nullable|string|max:50',
            'sort_order' => 'integer|min:0',
            'status' => 'integer|in:0,1',
        ]);

        $express->update($validated);

        return redirect()->route('soho.express.index')->with('success', '快递公司更新成功');
    }

    public function destroy(ExpressCompany $express)
    {
        $express->delete();
        return redirect()->route('soho.express.index')->with('success', '快递公司删除成功');
    }
}