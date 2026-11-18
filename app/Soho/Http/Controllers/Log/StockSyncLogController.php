<?php

namespace App\Soho\Http\Controllers\Log;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\StockSyncLog;
use Illuminate\Http\Request;

class StockSyncLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = StockSyncLog::with(['shop', 'systemGoods'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('log.stock-sync', compact('logs'));
    }
}