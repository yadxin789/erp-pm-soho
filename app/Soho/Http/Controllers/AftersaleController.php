<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\OrderAfterSale;
use Illuminate\Http\Request;

class AftersaleController extends Controller
{
    public function index(Request $request)
    {
        $aftersales = OrderAfterSale::with(['platformOrder', 'shop'])
            ->when($request->search, function($query) use ($request) {
                $query->where('after_sale_no', 'like', "%{$request->search}%")
                    ->orWhere('buyer_nick', 'like', "%{$request->search}%");
            })
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('aftersale.index', compact('aftersales'));
    }

    public function show(OrderAfterSale $aftersale)
    {
        $aftersale->load(['platformOrder', 'shop']);
        return view('aftersale.show', compact('aftersale'));
    }

    public function handle(Request $request, OrderAfterSale $aftersale)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'remark' => 'nullable|string|max:500',
        ]);

        $aftersale->update([
            'status' => $validated['action'] === 'approve' ? 'completed' : 'rejected',
            'handle_result' => $validated['remark'],
            'handle_time' => now(),
            'operator_id' => auth()->id(),
        ]);

        $message = $validated['action'] === 'approve' ? '售后申请已批准' : '售后申请已拒绝';
        return back()->with('success', $message);
    }

    /**
     * 待处理售后
     */
    public function pending(Request $request)
    {
        $aftersales = OrderAfterSale::with(['platformOrder', 'shop'])
            ->where('status', 'pending')
            ->when($request->search, function($query) use ($request) {
                $query->where('after_sale_no', 'like', "%{$request->search}%")
                    ->orWhere('buyer_nick', 'like', "%{$request->search}%");
            })
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('aftersale.pending', compact('aftersales'));
    }

    /**
     * 退款记录
     */
    public function refunds(Request $request)
    {
        $refunds = OrderAfterSale::with(['platformOrder', 'shop'])
            ->where('type', 'refund')
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

        return view('aftersale.refunds', compact('refunds'));
    }
}