<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class FulfillmentExceptionController extends Controller
{
    /**
     * 作业异常列表
     * 支持按异常类型、级别、状态筛选
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();

        $exceptions = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'packer', 'items'])
            ->whereIn('status', ['pick_exception', 'check_exception', 'pack_exception'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                })->orWhere('remark', 'like', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->exception_type, function ($query) use ($request) {
                $query->where('status', $request->exception_type);
            })
            ->when($request->severity, function ($query) use ($request) {
                $query->where('priority', '>=', $request->severity);
            })
            ->when($request->handler_status, function ($query) use ($request) {
                if ($request->handler_status === 'unassigned') {
                    $query->whereNull('operator_id');
                } elseif ($request->handler_status === 'assigned') {
                    $query->whereNotNull('operator_id');
                }
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('updated_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('updated_at', '<=', $request->date_end);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('fulfillment.exception.index', compact('exceptions', 'warehouses'));
    }
}