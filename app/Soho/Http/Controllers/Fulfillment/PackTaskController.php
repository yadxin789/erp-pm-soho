<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class PackTaskController extends Controller
{
    /**
     * 待打包任务列表
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();

        $packTasks = FulfillmentOrder::with(['order', 'warehouse', 'picker', 'items'])
            ->where('status', 'checked')
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
                $query->whereDate('updated_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('updated_at', '<=', $request->date_end);
            })
            ->orderBy('updated_at', 'asc')
            ->paginate(20);

        return view('fulfillment.pack.index', compact('packTasks', 'warehouses'));
    }

    /**
     * 扫码打包页面
     */
    public function scan()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.pack.scan', compact('warehouses'));
    }

    /**
     * 包材选择页面
     */
    public function material()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.pack.material', compact('warehouses'));
    }

    /**
     * 称重录入页面
     */
    public function weight()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.pack.weight', compact('warehouses'));
    }

    /**
     * 包裹生成页面
     */
    public function parcel()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.pack.parcel', compact('warehouses'));
    }

    /**
     * 打包异常列表
     */
    public function exception(Request $request)
    {
        $warehouses = Warehouse::all();

        $exceptions = FulfillmentOrder::with(['order', 'warehouse', 'packer', 'items'])
            ->where('status', 'pack_exception')
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

        return view('fulfillment.pack.exception', compact('exceptions', 'warehouses'));
    }

    /**
     * 已完成打包列表
     */
    public function completed(Request $request)
    {
        $warehouses = Warehouse::all();

        $completedTasks = FulfillmentOrder::with(['order', 'warehouse', 'packer', 'items'])
            ->where('status', 'packed')
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
                $query->whereDate('packed_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('packed_at', '<=', $request->date_end);
            })
            ->orderBy('packed_at', 'desc')
            ->paginate(20);

        return view('fulfillment.pack.completed', compact('completedTasks', 'warehouses'));
    }
}