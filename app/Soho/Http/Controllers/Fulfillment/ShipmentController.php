<?php

namespace App\Soho\Http\Controllers\Fulfillment;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\OrderPackage;
use App\Soho\Models\FulfillmentOrder;
use App\Soho\Models\Warehouse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * 待发货包裹列表
     */
    public function pending(Request $request)
    {
        $warehouses = Warehouse::all();

        $packages = OrderPackage::with(['order', 'warehouse', 'carrier'])
            ->where('status', 'pending')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                })->orWhere('package_number', 'like', "%{$request->search}%")
                  ->orWhere('tracking_number', 'like', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->carrier_id, function ($query) use ($request) {
                $query->where('carrier_id', $request->carrier_id);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('fulfillment.shipment.pending', compact('packages', 'warehouses'));
    }

    /**
     * 待推送发货列表
     */
    public function pushPending(Request $request)
    {
        $warehouses = Warehouse::all();

        $packages = OrderPackage::with(['order', 'warehouse', 'carrier'])
            ->where('status', 'push_pending')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                })->orWhere('package_number', 'like', "%{$request->search}%")
                  ->orWhere('tracking_number', 'like', "%{$request->search}%");
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
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('fulfillment.shipment.push-pending', compact('packages', 'warehouses'));
    }

    /**
     * 推送失败发货列表
     */
    public function pushFailed(Request $request)
    {
        $warehouses = Warehouse::all();

        $packages = OrderPackage::with(['order', 'warehouse', 'carrier'])
            ->where('status', 'push_failed')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('platform_order_no', 'like', "%{$request->search}%")
                        ->orWhere('system_order_no', 'like', "%{$request->search}%");
                })->orWhere('package_number', 'like', "%{$request->search}%")
                  ->orWhere('tracking_number', 'like', "%{$request->search}%");
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

        return view('fulfillment.shipment.push-failed', compact('packages', 'warehouses'));
    }

    /**
     * 发货回传页面
     */
    public function callback()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.shipment.callback', compact('warehouses'));
    }

    /**
     * 批量回传页面
     */
    public function batchCallback()
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('fulfillment.shipment.batch-callback', compact('warehouses'));
    }
}