<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ShopSyncCursor;
use Illuminate\Http\Request;

class SyncCursorController extends Controller
{
    /**
     * 同步游标列表
     */
    public function index(Request $request)
    {
        $syncCursors = ShopSyncCursor::with(['tenant', 'shop'])
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->sync_type, function($query) use ($request) {
                $query->where('sync_type', $request->sync_type);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->is_enabled !== null && $request->is_enabled !== '', function($query) use ($request) {
                $query->where('is_enabled', $request->is_enabled);
            })
            ->orderBy('shop_id')
            ->orderBy('sync_type')
            ->paginate(20);

        return view('sync-cursors.index', compact('syncCursors'));
    }

    /**
     * 重置同步游标
     */
    public function reset(ShopSyncCursor $shopSyncCursor)
    {
        $shopSyncCursor->update([
            'cursor' => null,
            'last_success_at' => null,
            'last_failure_at' => null,
            'retry_count' => 0,
            'consecutive_failures' => 0,
            'last_error_message' => null,
            'last_error_code' => null,
            'status' => 'pending',
        ]);

        return back()->with('success', '同步游标已重置');
    }
}