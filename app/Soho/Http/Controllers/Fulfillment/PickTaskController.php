<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class PickTaskController extends Controller
{
    /**
     * 待拣货任务列表
     * 查询待拣货的订单，支持按订单号、仓库、时间筛选
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();

        $pickTasks = FulfillmentOrder::with(['order', 'warehouse', 'items'])
            ->where('status', 'pending')
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
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('fulfillment.pick.index', compact('pickTasks', 'warehouses'));
    }

    /**
     * 扫码拣货页面
     */
    public function scan()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.pick.scan', compact('warehouses'));
    }

    /**
     * 拣货异常列表
     */
    public function exception(Request $request)
    {
        $warehouses = Warehouse::all();

        $exceptions = FulfillmentOrder::with(['order', 'warehouse', 'items'])
            ->where('status', 'pick_exception')
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

        return view('fulfillment.pick.exception', compact('exceptions', 'warehouses'));
    }

    /**
     * 执行中任务列表
     */
    public function progress(Request $request)
    {
        $warehouses = Warehouse::all();

        $progressTasks = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'picking')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                });
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->picker_id, function ($query) use ($request) {
                $query->where('picker_id', $request->picker_id);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('fulfillment.pick.progress', compact('progressTasks', 'warehouses'));
    }

    /**
     * 已完成任务列表
     */
    public function completed(Request $request)
    {
        $warehouses = Warehouse::all();

        $completedTasks = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'picked')
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
                $query->whereDate('picked_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('picked_at', '<=', $request->date_end);
            })
            ->orderBy('picked_at', 'desc')
            ->paginate(20);

        return view('fulfillment.pick.completed', compact('completedTasks', 'warehouses'));
    }
}