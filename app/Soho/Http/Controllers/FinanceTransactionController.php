<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\FinancialTransaction;
use Illuminate\Http\Request;

class FinanceTransactionController extends Controller
{
    /**
     * 财务交易流水列表
     */
    public function index(Request $request)
    {
        $transactions = FinancialTransaction::with(['tenant', 'shop', 'order', 'settlement'])
            ->when($request->search, function($query) use ($request) {
                $query->where('transaction_no', 'like', "%{$request->search}%")
                    ->orWhere('platform_trade_no', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->transaction_type, function($query) use ($request) {
                $query->where('transaction_type', $request->transaction_type);
            })
            ->when($request->direction, function($query) use ($request) {
                $query->where('direction', $request->direction);
            })
            ->when($request->currency, function($query) use ($request) {
                $query->where('currency', $request->currency);
            })
            ->when($request->reconcile_status, function($query) use ($request) {
                $query->where('reconcile_status', $request->reconcile_status);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('transaction_time', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('transaction_time', '<=', $request->date_end);
            })
            ->orderBy('transaction_time', 'desc')
            ->paginate(20);

        return view('finance.transactions', compact('transactions'));
    }
}