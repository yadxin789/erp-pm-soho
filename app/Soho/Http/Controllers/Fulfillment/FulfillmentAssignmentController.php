<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class FulfillmentAssignmentController extends Controller
{
    /**
     * 待分配作业列表
     * 查询未分配操作员的履约任务，支持按订单号、仓库、作业类型筛选
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();

        $assignments = FulfillmentOrder::with(['order', 'warehouse', 'items'])
            ->whereNull('operator_id')
            ->whereIn('status', ['pending', 'picked', 'checked'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%")
                        ->orWhere('buyer_nick', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->task_type, function ($query) use ($request) {
                $query->where('status', $request->task_type);
            })
            ->when($request->priority, function ($query) use ($request) {
                $query->where('priority', '>=', $request->priority);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('fulfillment.assignment.index', compact('assignments', 'warehouses'));
    }
}