<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\TaxEntity;
use Illuminate\Http\Request;

class TaxEntityController extends Controller
{
    /**
     * 税务主体列表
     */
    public function index(Request $request)
    {
        $taxEntities = TaxEntity::when($request->search, function($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('tax_id', 'like', "%{$request->search}%");
        })
        ->when($request->entity_type, function($query) use ($request) {
            $query->where('entity_type', $request->entity_type);
        })
        ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('tax-entities.index', compact('taxEntities'));
    }

    /**
     * 创建税务主体
     */
    public function create()
    {
        return view('tax-entities.create');
    }

    /**
     * 保存新税务主体
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string|max:50',
            'tax_id' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'status' => 'boolean',
        ]);

        $validated['status'] = $validated['status'] ?? true;
        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;

        TaxEntity::create($validated);

        return redirect()->route('soho.tax_entities.index')->with('success', '税务主体创建成功');
    }

    /**
     * 编辑税务主体
     */
    public function edit(TaxEntity $taxEntity)
    {
        return view('tax-entities.edit', compact('taxEntity'));
    }

    /**
     * 更新税务主体
     */
    public function update(Request $request, TaxEntity $taxEntity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string|max:50',
            'tax_id' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'status' => 'boolean',
        ]);

        $taxEntity->update($validated);

        return redirect()->route('soho.tax_entities.index')->with('success', '税务主体更新成功');
    }
}