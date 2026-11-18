<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\PlatformOrder;
use App\Soho\Models\FinanceReconcile;
use App\Soho\Models\FinanceSettle;
use App\Soho\Models\OrderAfterSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * 收款记录
     */
    public function receive(Request $request)
    {
        $records = PlatformOrder::with(['shop'])
            ->where('order_status', 'paid')
            ->when($request->search, function($query) use ($request) {
                $query->where('platform_order_no', 'like', "%{$request->search}%")
                    ->orWhere('system_order_no', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('pay_time', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('pay_time', '<=', $request->date_end);
            })
            ->orderBy('pay_time', 'desc')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total_amount' => $records->sum('pay_amount'),
            'order_count' => $records->count(),
        ];

        return view('finance.receive', compact('records', 'stats'));
    }

    /**
     * 对账管理
     */
    public function reconcile(Request $request)
    {
        $reconciles = FinanceReconcile::with(['shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('finance.reconcile', compact('reconciles'));
    }

    /**
     * 保存对账记录
     */
    public function storeReconcile(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:soho_shops,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'amount' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:500',
        ]);

        $reconcileNo = 'REC' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        FinanceReconcile::create([
            'reconcile_no' => $reconcileNo,
            'shop_id' => $request->shop_id,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'amount' => $request->amount,
            'status' => 'pending',
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        return redirect()->route('soho.finance.reconcile')->with('success', '对账记录已创建');
    }

    /**
     * 结算管理
     */
    public function settle(Request $request)
    {
        $settles = FinanceSettle::with(['shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('finance.settle', compact('settles'));
    }

    /**
     * 保存结算记录
     */
    public function storeSettle(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:soho_shops,id',
            'amount' => 'required|numeric|min:0',
            'settle_method' => 'required|in:bank,alipay,wechat',
            'remark' => 'nullable|string|max:500',
        ]);

        $settleNo = 'STL' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        FinanceSettle::create([
            'settle_no' => $settleNo,
            'shop_id' => $request->shop_id,
            'amount' => $request->amount,
            'settle_method' => $request->settle_method,
            'status' => 'pending',
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        return redirect()->route('soho.finance.settle')->with('success', '结算记录已创建');
    }

    /**
     * 退款记录
     */
    public function refunds(Request $request)
    {
        $refunds = OrderAfterSale::with(['platformOrder', 'shop'])
            ->where('type', 'refund')
            ->when($request->shop_id, function($query) use ($request) {
                $query->whereHas('shop', function($q) use ($request) {
                    $q->where('id', $request->shop_id);
                });
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('finance.refunds', compact('refunds'));
    }
}
