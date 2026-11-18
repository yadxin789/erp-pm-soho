<?php

namespace App\Soho\Http\Controllers\Api\Order;

use App\Soho\Models\Inventory;
use App\Soho\Models\OrderPackage;
use App\Soho\Models\OrderStatusLog;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 订单API控制器
 * 提供订单查询、状态更新、发货等功能
 */
class OrderController extends Controller
{
    /**
     * 获取订单列表
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|in:taobao,jd,pinduoduo,amazon',
            'shop_id' => 'nullable|integer|exists:soho_shops,id',
            'order_status' => 'nullable|string|in:pending,paid,shipped,completed,cancelled,frozen',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $orders = PlatformOrder::query();

        if ($request->has('platform')) {
            $orders->where('platform', $request->platform);
        }
        if ($request->has('shop_id')) {
            $orders->where('shop_id', $request->shop_id);
        }
        if ($request->has('order_status')) {
            $orders->where('order_status', $request->order_status);
        }

        return response()->json([
            'data' => $orders->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取订单详情
     */
    public function show(PlatformOrder $order)
    {
        return response()->json([
            'data' => $order->load('orderItems', 'packages', 'statusLogs'),
        ]);
    }

    /**
     * 更新订单状态
     * 自动记录状态变更日志
     */
    public function updateStatus(Request $request, PlatformOrder $order)
    {
        $request->validate([
            'status' => 'required|string',
            'remark' => 'nullable|string|max:255',
        ]);

        $newStatus = $request->status;
        $remark = $request->remark ?? '';

        // 如果状态未变化，直接返回
        if ($order->order_status === $newStatus) {
            return response()->json([
                'data' => $order,
                'message' => '订单状态未变化',
            ]);
        }

        try {
            DB::beginTransaction();

            // 记录状态变更前的值
            $oldStatus = $order->order_status;

            // 更新订单状态
            $order->order_status = $newStatus;
            $order->save();

            // 记录状态变更日志
            OrderStatusLog::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'operator_type' => auth()->check() ? 'user' : 'system',
                'operator_id' => auth()->id() ?? null,
                'source' => 'api',
                'remark' => $remark,
            ]);

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'platform_order_no' => $order->platform_order_no,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
            ]);

            return response()->json([
                'data' => $order,
                'message' => '订单状态更新成功',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order status update failed', [
                'order_id' => $order->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '订单状态更新失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 发货
     * 创建发货记录、更新订单状态、记录状态日志、释放库存锁定
     */
    public function ship(Request $request, PlatformOrder $order)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'carrier_id' => 'nullable|integer',
            'warehouse_id' => 'nullable|integer',
            'remark' => 'nullable|string|max:255',
        ]);

        $trackingNumber = $request->tracking_number;
        $carrierId = $request->carrier_id;
        $warehouseId = $request->warehouse_id;
        $remark = $request->remark ?? '';

        // 检查订单状态是否允许发货
        if (!in_array($order->order_status, ['paid', 'processing'])) {
            return response()->json([
                'error' => '订单状态不允许发货',
                'current_status' => $order->order_status,
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 创建发货包裹记录
            $package = OrderPackage::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'package_number' => 'PKG' . date('YmdHis') . rand(1000, 9999),
                'warehouse_id' => $warehouseId,
                'carrier_id' => $carrierId,
                'tracking_number' => $trackingNumber,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);

            // 记录发货前的订单状态
            $oldStatus = $order->order_status;

            // 更新订单状态为已发货
            $order->order_status = 'shipped';
            $order->ship_time = now();
            $order->save();

            // 记录订单状态变更日志
            OrderStatusLog::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'shipped',
                'operator_type' => auth()->check() ? 'user' : 'system',
                'operator_id' => auth()->id() ?? null,
                'source' => 'api',
                'remark' => '发货，运单号: ' . $trackingNumber,
            ]);

            // 释放订单占用的库存锁定
            $this->releaseInventoryLock($order);

            DB::commit();

            Log::info('Order shipped successfully', [
                'order_id' => $order->id,
                'platform_order_no' => $order->platform_order_no,
                'tracking_number' => $trackingNumber,
                'package_id' => $package->id,
            ]);

            return response()->json([
                'data' => $order->load('packages'),
                'package' => $package,
                'message' => '发货信息已记录',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order ship failed', [
                'order_id' => $order->id,
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '发货失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 释放订单占用的库存锁定
     * 发货后将订单占用的库存从locked_quantity转移到actual_quantity的扣减
     *
     * @param PlatformOrder $order 订单模型
     */
    protected function releaseInventoryLock(PlatformOrder $order)
    {
        $order->loadMissing('orderItems');

        $inventoryUpdates = [];
        foreach ($order->orderItems as $item) {
            if (!$item->warehouse_id || !$item->sku_id || $item->quantity <= 0) {
                continue;
            }

            $inventory = Inventory::where('sku_id', $item->sku_id)
                ->where('warehouse_id', $item->warehouse_id)
                ->first();

            if (!$inventory) {
                continue;
            }

            // 扣减实际库存并释放锁定库存
            $inventory->actual_quantity -= $item->quantity;
            $inventory->locked_quantity = max(0, $inventory->locked_quantity - $item->quantity);
            $inventory->save();

            Log::info('Inventory lock released after shipping', [
                'order_id' => $order->id,
                'sku_id' => $item->sku_id,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => $item->quantity,
            ]);
        }
    }

    /**
     * 获取订单包裹列表
     */
    public function packages(PlatformOrder $order)
    {
        return response()->json([
            'data' => $order->packages,
        ]);
    }

    /**
     * 获取订单状态日志
     */
    public function logs(PlatformOrder $order)
    {
        return response()->json([
            'data' => $order->statusLogs()->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }
}