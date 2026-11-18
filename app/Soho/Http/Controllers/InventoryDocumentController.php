<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\InventoryDocument;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryDocumentController extends Controller
{
    /**
     * 库存单据列表
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();

        $documents = InventoryDocument::with(['tenant', 'warehouse', 'operator'])
            ->when($request->search, function($query) use ($request) {
                $query->where('document_no', 'like', "%{$request->search}%")
                    ->orWhere('remark', 'like', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->document_type, function($query) use ($request) {
                $query->where('document_type', $request->document_type);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('inventory.documents', compact('documents', 'warehouses'));
    }

    /**
     * 保存库存单据
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:soho_warehouses,id',
            'document_type' => 'required|string|max:50',
            'total_quantity' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:500',
        ]);

        $validated['document_no'] = 'DOC' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'draft';
        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;
        $validated['operator_id'] = auth()->id();

        InventoryDocument::create($validated);

        return redirect()->route('inventory.documents')->with('success', '库存单据创建成功');
    }
}