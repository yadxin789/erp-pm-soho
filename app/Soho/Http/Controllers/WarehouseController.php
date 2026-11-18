<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('warehouse.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouse.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:soho_warehouses',
            'type' => 'required|string|max:30',
            'country_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'priority' => 'integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        Warehouse::create($validated);

        return redirect()->route('soho.warehouse.index')->with('success', '仓库创建成功');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:50', Rule::unique('soho_warehouses')->ignore($warehouse->id)],
            'type' => 'required|string|max:30',
            'country_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'priority' => 'integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        $warehouse->update($validated);

        return redirect()->route('soho.warehouse.index')->with('success', '仓库更新成功');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->inventories()->count() > 0) {
            return back()->with('error', '该仓库有库存记录，无法删除');
        }

        $warehouse->delete();
        return redirect()->route('soho.warehouse.index')->with('success', '仓库删除成功');
    }
}