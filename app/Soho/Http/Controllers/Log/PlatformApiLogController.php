<?php

namespace App\Soho\Http\Controllers\Log;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PlatformApiLog;
use Illuminate\Http\Request;

class PlatformApiLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = PlatformApiLog::with(['shop'])
            ->when($request->search, function($query) use ($request) {
                $query->where('order_number', 'like', "%{$request->search}%")
                    ->orWhere('api_method', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('log.platform-api', compact('logs'));
    }
}