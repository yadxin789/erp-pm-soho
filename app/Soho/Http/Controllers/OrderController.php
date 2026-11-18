<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\PlatformOrder;
use App\Soho\Models\ExpressCompany;
use App\Soho\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * 订单列表
     */
    public function index(Request $request)
    {
        $orders = PlatformOrder::with(['shop'])
            ->when($request->search, function($query) use ($request) {
                $query->where('platform_order_no', 'like', "%{$request->search}%")
                    ->orWhere('system_order_no', 'like', "%{$request->search}%")
                    ->orWhere('buyer_nick', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->platform, function($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('order_status', $request->status);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('order_created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('order_created_at', '<=', $request->date_end);
            })
            ->whereNotNull('order_created_at')
            ->orderBy('order_created_at', 'desc')
            ->paginate(20);

        return view('order.index', compact('orders'));
    }

    /**
     * 订单详情
     */
    public function show(PlatformOrder $order)
    {
        $order->load(['shop', 'orderItems', 'packages', 'shipRecords.expressCompany', 'statusLogs', 'afterSales']);
        $expressCompanies = ExpressCompany::where('status', true)->orderBy('sort_order')->get();

        return view('order.show', compact('order', 'expressCompanies'));
    }

    /**
     * 单订单发货 - 创建发货记录并触发异步推送
     */
    public function ship(Request $request, PlatformOrder $order)
    {
        $request->validate([
            'express_company_id' => 'required|exists:soho_express_company,id',
            'tracking_number' => 'required|string|max:200',
        ]);

        if ($order->order_status !== 'paid') {
            return back()->with('error', '仅已付款订单可以发货');
        }

        // 创建发货记录
        $order->shipRecords()->create([
            'express_company_id' => $request->express_company_id,
            'tracking_number' => $request->tracking_number,
            'ship_status' => 'pending',
            'operator_id' => auth()->id(),
            'ship_time' => now(),
        ]);

        // 更新订单状态为已发货
        $order->update([
            'order_status' => 'shipped',
            'ship_time' => now(),
        ]);

        return back()->with('success', '发货信息已记录，物流信息将异步推送到平台');
    }

    /**
     * 批量发货 - 通过 CSV 文件或文本导入物流单号
     */
    public function batchShip(Request $request)
    {
        $request->validate([
            'express_company_id' => 'nullable|exists:soho_express_company,id',
            'shipping_file' => 'nullable|file|mimes:csv,txt',
            'shipping_content' => 'nullable|string',
        ]);

        $defaultExpressId = $request->express_company_id;
        $lines = [];

        // 从上传文件读取
        if ($request->hasFile('shipping_file')) {
            $filePath = $request->file('shipping_file')->getRealPath();
            $content = file_get_contents($filePath);
            $content = ltrim($content, "\xEF\xBB\xBF"); // 移除 BOM
            $lines = array_merge($lines, array_filter(explode("\n", str_replace("\r\n", "\n", $content))));
        }

        // 从文本框读取
        if (!empty($request->shipping_content)) {
            $textLines = array_filter(explode("\n", str_replace("\r\n", "\n", $request->shipping_content)));
            $lines = array_merge($lines, $textLines);
        }

        if (empty($lines)) {
            return back()->with('error', '未读取到有效物流数据');
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = str_getcsv($line);
            if (count($parts) < 2) {
                $failCount++;
                $errors[] = "格式错误: {$line}";
                continue;
            }

            $platformOrderNo = trim($parts[0]);
            $trackingNumber = trim($parts[1]);
            $expressCompanyId = !empty($parts[2]) ? (int) trim($parts[2]) : $defaultExpressId;

            if (!$expressCompanyId) {
                $failCount++;
                $errors[] = "缺少快递公司ID: {$platformOrderNo}";
                continue;
            }

            $order = PlatformOrder::where('platform_order_no', $platformOrderNo)->first();
            if (!$order) {
                $failCount++;
                $errors[] = "订单不存在: {$platformOrderNo}";
                continue;
            }

            if ($order->order_status === 'shipped' || $order->order_status === 'completed') {
                $failCount++;
                $errors[] = "订单已发货: {$platformOrderNo}";
                continue;
            }

            // 创建发货记录
            $order->shipRecords()->create([
                'express_company_id' => $expressCompanyId,
                'tracking_number' => $trackingNumber,
                'ship_status' => 'pending',
                'operator_id' => auth()->id(),
                'ship_time' => now(),
            ]);

            // 更新订单状态
            $order->update([
                'order_status' => 'shipped',
                'ship_time' => now(),
            ]);

            $successCount++;
        }

        $message = "批量发货完成：成功 {$successCount} 条";
        if ($failCount > 0) {
            $message .= "，失败 {$failCount} 条";
        }
        if (!empty($errors)) {
            $message .= "\n错误: " . implode("\n", array_slice($errors, 0, 10));
        }

        return back()->with($failCount > 0 ? 'warning' : 'success', $message);
    }

    /**
     * 冻结/标记异常订单
     */
    public function freeze(Request $request, PlatformOrder $order)
    {
        if (in_array($order->order_status, ['frozen', 'completed', 'cancelled'])) {
            return back()->with('error', '当前订单状态不支持冻结操作');
        }

        $request->validate([
            'abnormal_reason' => 'nullable|string|max:500',
        ]);

        $order->update([
            'order_status' => 'frozen',
            'is_abnormal' => true,
            'abnormal_reason' => $request->abnormal_reason,
        ]);

        return back()->with('success', '订单已冻结，标记为异常');
    }

    /**
     * 添加/修改订单备注
     */
    public function remark(Request $request, PlatformOrder $order)
    {
        $request->validate([
            'operator_remark' => 'required|string|max:500',
        ]);

        $order->update(['operator_remark' => $request->operator_remark]);

        return back()->with('success', '备注已保存');
    }

    /**
     * 待付款订单
     */
    public function pendingPayment(Request $request)
    {
        $orders = $this->getOrdersByStatus($request, ['pending', 'unpaid']);
        return view('order.pending-payment', compact('orders'));
    }

    /**
     * 待发货订单
     */
    public function pendingShip(Request $request)
    {
        $orders = $this->getOrdersByStatus($request, ['paid', 'pending_ship']);
        return view('order.pending-ship', compact('orders'));
    }

    /**
     * 已发货订单
     */
    public function shipped(Request $request)
    {
        $orders = $this->getOrdersByStatus($request, ['shipped']);
        return view('order.shipped', compact('orders'));
    }

    /**
     * 已完成订单
     */
    public function completed(Request $request)
    {
        $orders = $this->getOrdersByStatus($request, ['completed']);
        return view('order.completed', compact('orders'));
    }

    /**
     * 已取消订单
     */
    public function cancelled(Request $request)
    {
        $orders = $this->getOrdersByStatus($request, ['cancelled', 'cancel']);
        return view('order.cancelled', compact('orders'));
    }

    /**
     * 异常订单
     */
    public function exception(Request $request)
    {
        $orders = PlatformOrder::with(['shop'])
            ->where(function($query) {
                $query->where('is_abnormal', true)
                    ->orWhere('order_status', 'frozen');
            })
            ->when($request->search, function($query) use ($request) {
                $query->where('platform_order_no', 'like', "%{$request->search}%")
                    ->orWhere('system_order_no', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->orderBy('order_created_at', 'desc')
            ->paginate(20);

        return view('order.exception', compact('orders'));
    }

    /**
     * 导出订单
     */
    public function export(Request $request)
    {
        $query = PlatformOrder::with(['shop']);

        // 根据请求筛选条件导出
        if ($request->status) {
            $query->where('order_status', $request->status);
        }
        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($request->date_start) {
            $query->whereDate('order_created_at', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $query->whereDate('order_created_at', '<=', $request->date_end);
        }

        $orders = $query->orderBy('order_created_at', 'desc')->get();

        // 生成 CSV 文件
        $filename = 'orders_' . now()->format('Ymd_His') . '.csv';
        $headers = ['订单编号', '店铺', '平台', '买家', '金额', '状态', '创建时间'];

        $handle = fopen('php://temp', 'w');
        fputcsv($handle, $headers);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->platform_order_no,
                $order->shop->shop_name ?? '',
                $order->platform,
                $order->buyer_nick,
                $order->pay_amount,
                $order->order_status,
                $order->order_created_at,
            ]);
        }

        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * 取消订单
     */
    public function cancel(Request $request, PlatformOrder $order)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        if (!in_array($order->order_status, ['pending', 'unpaid', 'paid'])) {
            return back()->with('error', '当前订单状态不支持取消');
        }

        $order->update([
            'order_status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
            'cancel_time' => now(),
        ]);

        // 记录状态变更日志
        $order->statusLogs()->create([
            'from_status' => $order->getOriginal('order_status'),
            'to_status' => 'cancelled',
            'operator_id' => auth()->id(),
            'remark' => '取消订单：' . $request->cancel_reason,
        ]);

        return back()->with('success', '订单已取消');
    }

    /**
     * 根据状态获取订单的通用方法
     */
    protected function getOrdersByStatus(Request $request, array $statuses)
    {
        $query = PlatformOrder::with(['shop'])
            ->whereIn('order_status', $statuses);

        $query->when($request->search, function($q) use ($request) {
            $q->where('platform_order_no', 'like', "%{$request->search}%")
                ->orWhere('system_order_no', 'like', "%{$request->search}%")
                ->orWhere('buyer_nick', 'like', "%{$request->search}%");
        });

        $query->when($request->shop_id, function($q) use ($request) {
            $q->where('shop_id', $request->shop_id);
        });

        $query->when($request->platform, function($q) use ($request) {
            $q->where('platform', $request->platform);
        });

        $query->orderBy('order_created_at', 'desc');

        return $query->paginate(20);
    }
}