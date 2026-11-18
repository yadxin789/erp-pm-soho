<?php

namespace App\Soho\Http\Controllers\Api\Warehouse;

use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 仓库API控制器
 */
class WarehouseController extends Controller
{
    /**
     * 获取仓库列表
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::query();

        if ($request->has('type')) {
            $warehouses->where('type', $request->type);
        }
        if ($request->has('status')) {
            $warehouses->where('status', $request->status);
        }

        return response()->json([
            'data' => $warehouses->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取仓库详情
     */
    public function show(Warehouse $warehouse)
    {
        return response()->json([
            'data' => $warehouse,
        ]);
    }

    /**
     * 创建仓库
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:soho_warehouses,code',
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:domestic,overseas,fba,third_party,virtual',
            'country_code' => 'nullable|string|size:2',
            'address' => 'nullable|string|max:500',
            'contact_name' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'priority' => 'nullable|integer|min:1|max:10',
            'status' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $warehouse = Warehouse::create($validated);

        return response()->json([
            'data' => $warehouse,
            'message' => '仓库创建成功',
        ], 201);
    }

    /**
     * 更新仓库
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:soho_warehouses,code,' . $warehouse->id,
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|string|in:domestic,overseas,fba,third_party,virtual',
            'country_code' => 'sometimes|nullable|string|size:2',
            'address' => 'sometimes|nullable|string|max:500',
            'contact_name' => 'sometimes|nullable|string|max:50',
            'contact_phone' => 'sometimes|nullable|string|max:20',
            'priority' => 'sometimes|nullable|integer|min:1|max:10',
            'status' => 'sometimes|boolean',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'data' => $warehouse,
            'message' => '仓库更新成功',
        ]);
    }

    /**
     * 删除仓库
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json([
            'message' => '仓库删除成功',
        ]);
    }
}