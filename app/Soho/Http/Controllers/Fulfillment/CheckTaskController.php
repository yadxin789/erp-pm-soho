<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class CheckTaskController extends Controller
{
    /**
     * 待复核任务列表
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();

        $checkTasks = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'picked')
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
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('picked_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('picked_at', '<=', $request->date_end);
            })
            ->orderBy('picked_at', 'asc')
            ->paginate(20);

        return view('fulfillment.check.index', compact('checkTasks', 'warehouses'));
    }

    /**
     * 扫码复核页面
     */
    public function scan()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.check.scan', compact('warehouses'));
    }

    /**
     * 复核异常列表
     */
    public function exception(Request $request)
    {
        $warehouses = Warehouse::all();

        $exceptions = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'check_exception')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->exception_type, function ($query) use ($request) {
                $query->where('remark', 'like', "%{$request->exception_type}%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('fulfillment.check.exception', compact('exceptions', 'warehouses'));
    }

    /**
     * 已完成复核列表
     */
    public function completed(Request $request)
    {
        $warehouses = Warehouse::all();

        $completedTasks = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'checked')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('updated_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('updated_at', '<=', $request->date_end);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('fulfillment.check.completed', compact('completedTasks', 'warehouses'));
    }

    /**
     * 复核记录
     */
    public function record(Request $request)
    {
        $warehouses = Warehouse::all();

        $records = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->whereIn('status', ['checked', 'check_exception'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('updated_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('updated_at', '<=', $request->date_end);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('fulfillment.check.record', compact('records', 'warehouses'));
    }
}